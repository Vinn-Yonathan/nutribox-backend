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

use function Symfony\Component\Clock\now;

class TransactionServiceImpl implements TransactionService
{
    function create(array $transactionData, User $user): Transaction
    {
        return DB::transaction(function () use ($transactionData, $user) {
            $menuIds = array_column($transactionData['menus'], 'menu_id');
            $menus = Menu::whereIn('id', $menuIds)->lockForUpdate()->get();
            foreach ($transactionData['menus'] as $item) {
                //check stock
                if ($item['quantity'] > $menus->find($item['menu_id'])->quantity) {
                    throw new HttpResponseException(response([
                        'errors' => [
                            'message' => ["Menu's stock is unavailable"]
                        ]
                    ], 409));
                }
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'payment_method' => $transactionData['payment_method'],
                'status' => 'pending',
                'total_price' => $transactionData['total_price']
            ]);

            $menuData = array_map(function ($item) use ($transaction, $menus) {
                $item['transaction_id'] = $transaction->id;
                $item['created_at'] = now();

                $menu = $menus->find($item['menu_id']);
                $menu->decrement('quantity', $item['quantity']);
                return $item;
            }, $transactionData['menus']);

            TransactionItem::insert($menuData);
            return $transaction;
        });
    }

    function getById(int $transactionId, ?User $user): ?Transaction
    {
        return $user ? $user->transactions()->with('transactionItems.menus')->find($transactionId) : Transaction::with('transactionItems.menus')->find($transactionId);
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
            ->paginate($filter['per_page'] ?? 10, $filter['page'] ?? 1);
    }


    function updateStatus(int $transactionId, ?User $user, string $status): ?Transaction
    {
        $transaction = $user ? $user->transactions()->with('transactionItems.menus')->find($transactionId) : Transaction::with('transactionItems.menus')->find($transactionId);
        if (!$transaction)
            return null;

        $transaction->update(['status' => $status]);
        return $transaction->fresh();
    }

    function delete(int $transactionId, ?User $user): ?bool
    {
        $transaction = $user ? $user->transactions()->find($transactionId) : Transaction::find($transactionId);
        if (!$transaction)
            return null;

        return $transaction->delete();
    }
}
