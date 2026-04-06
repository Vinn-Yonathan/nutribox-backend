<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionAddRequest;
use App\Http\Requests\TransactionUpdateRequest;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use PDO;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionController extends Controller
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    private function handleNotFound($data)
    {
        if ($data === null || ($data instanceof \Illuminate\Pagination\AbstractPaginator && $data->isEmpty())) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Data not found'
                    ]
                ]
            ], 404));
        }
        return;
    }

    public function add(TransactionAddRequest $request): JsonResponse
    {
        $user = Auth::user();
        $transactionData = $request->validated();

        $transaction = $this->transactionService->create($transactionData, $user);
        $this->handleNotFound($transaction);

        $transactionResource = new TransactionResource($transaction);

        return $transactionResource->response()->setStatusCode(201);
    }

    public function generateToken(int $id): JsonResponse
    {
        $snapToken = $this->transactionService->generateToken($id);
        $this->handleNotFound($snapToken);
        return response()->json([
            'data' => [
                'snap_token' => $snapToken
            ]
        ], 200);
    }

    public function getUserTransactions(): TransactionCollection
    {
        $user = Auth::user();
        $transactions = $this->transactionService->getList($user, []);
        $this->handleNotFound($transactions);
        return new TransactionCollection($transactions);
    }

    public function getTransactions(Request $request): TransactionCollection
    {
        $filter = $request->only([
            'user_id',
            'max_price',
            'min_price',
            'include_deleted',
            'status',
            'payment_method',
            'page',
            'size',
        ]);
        $transactions = $this->transactionService->getList(null, $filter);
        $this->handleNotFound($transactions);
        return new TransactionCollection($transactions);
    }
    public function getUserTransaction(int $id): TransactionResource
    {
        $user = Auth::user();
        $transaction = $this->transactionService->getById($id, $user);
        $this->handleNotFound($transaction);
        return new TransactionResource($transaction);
    }
    public function getTransaction(int $id): TransactionResource
    {
        $transaction = $this->transactionService->getById($id, null);
        $this->handleNotFound($transaction);
        return new TransactionResource($transaction);
    }

    public function update(TransactionUpdateRequest $request): TransactionResource
    {
        $data = $request->validated();
        $signature = hash(
            'sha512',
            $data['order_id'] . $data['status_code'] . (string) $data['gross_amount'] . env('MIDTRANS_SERVER_KEY')
        );

        if ($signature !== $data['signature_key']) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => ['Forbidden']
                ]
            ], 403));
        }

        $transaction = $this->transactionService->update($data);
        $this->handleNotFound($transaction);
        return new TransactionResource($transaction);
    }

    public function deleteUserTransaction(int $id): JsonResponse
    {
        $user = Auth::user();
        $transaction = $this->transactionService->delete($id, $user);
        $this->handleNotFound($transaction);

        return response()->json([
            'data' => 'true'
        ], 200);
    }
    public function deleteTransaction(int $id): JsonResponse
    {

        $transaction = $this->transactionService->delete($id, null);
        $this->handleNotFound($transaction);
        return response()->json([
            'data' => 'true'
        ], 200);
    }

}
