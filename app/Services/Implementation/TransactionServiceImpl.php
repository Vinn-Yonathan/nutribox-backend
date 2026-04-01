<?php

namespace App\Services\Implementation;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Queries\TransactionQueryBuilder;
use App\Services\TransactionService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function Symfony\Component\Clock\now;

class TransactionServiceImpl implements TransactionService
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    function create(array $transactionData, User $user): Transaction
    {
        return DB::transaction(function () use ($transactionData, $user) {
            $menuIds = array_column($transactionData['menus'], 'menu_id');
            $menus = Menu::whereIn('id', $menuIds)->lockForUpdate()->get();
            foreach ($transactionData['menus'] as $item) {
                //check stock
                if ($item['quantity'] > $menus->find($item['menu_id'])->stock) {
                    throw new HttpResponseException(response([
                        'errors' => [
                            'message' => ["Menu's stock is unavailable"]
                        ]
                    ], 409));
                }
            }

            $transaction = Transaction::create([
                'midtrans_id' => Str::uuid(),
                'user_id' => $user->id,
                'status' => 'pending',
                'total_price' => $transactionData['total_price']
            ]);

            $menuData = array_map(function ($item) use ($transaction, $menus) {
                $item['transaction_id'] = $transaction->id;
                $item['created_at'] = now();

                $menu = $menus->find($item['menu_id']);
                $menu->decrement('stock', $item['quantity']);
                return $item;
            }, $transactionData['menus']);

            TransactionItem::insert($menuData);

            $transaction->snap_token = Snap::getSnapToken([
                'transaction_details' => [
                    'order_id' => $transaction->midtrans_id,
                    'gross_amount' => $transaction->total_price,
                ]
            ]);


            return $transaction;
        });
    }

    function getById(int $transactionId, ?User $user): ?Transaction
    {
        return $user ? $user->transactions()->with('transactionItems.menu')->find($transactionId) : Transaction::with('transactionItems.menu')->find($transactionId);
    }

    function getList(?User $user, array $filter): LengthAwarePaginator
    {
        return (new TransactionQueryBuilder())
            ->filterByUser($user->id ?? $filter['user_id'] ?? null)
            ->filterByMinPrice($filter['min_price'] ?? null)
            ->filterByMaxPrice($filter['max_price'] ?? null)
            ->filterIncludeDeleted($filter['include_deleted'] ?? null)
            ->filterByStatus($filter['status'] ?? null)
            ->filterByPaymentMethod($filter['payment_method'] ?? null)
            ->paginate($filter['size'] ?? 10, $filter['page'] ?? 1);
    }

    function update(array $transactionData): ?Transaction
    {
        $transaction = Transaction::with('transactionItems.menu')->where("midtrans_id", $transactionData['order_id'])->first();
        if (!$transaction)
            return null;
        if ($transaction->status === 'paid')
            return $transaction;

        $transactionStatus = $transactionData['transaction_status'] === "settlement" ? "paid" : "pending";

        $transaction->update(['status' => $transactionStatus, 'payment_method' => $transactionData['payment_type']]);
        return $transaction->fresh();
    }

    function delete(int $transactionId, ?User $user): ?bool
    {
        return DB::transaction(function () use ($transactionId, $user) {
            $transaction = $user ? $user->transactions()->where('id', $transactionId)->lockForUpdate()->first() : Transaction::where('id', $transactionId)->lockForUpdate()->first();
            if (!$transaction) {
                return null;
            } else if ($transaction->status === "paid") {
                throw new HttpResponseException(response([
                    'errors' => [
                        'message' => ["Paid transactions cannot be cancelled"]
                    ]
                ], 409));
            }
            $menuIds = $transaction->transactionItems->pluck('menu_id');
            $menus = Menu::whereIn('id', $menuIds)->lockForUpdate()->get();
            foreach ($transaction->transactionItems as $item) {
                $menu = $menus->find($item['menu_id']);
                $menu->increment('stock', $item['quantity']);
            }

            return $transaction->delete();
        });
    }
}
