<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartAddRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartController extends Controller
{
    private CartService $cartService;
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    private function handleNotFound($data)
    {
        if ($data === null || ($data instanceof \Illuminate\Support\Collection && $data->isEmpty())) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ])->setStatusCode(404));
        }
        return;
    }

    public function addItem(CartAddRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        $cart = new Cart();

        if ($request->has('items')) {
            $cart = $this->cartService->addItemBulk($user, $data['items']);
        } else {
            $cart = $this->cartService->addItem($user, $data);
        }
        $cartResource = new CartResource($cart);
        return $cartResource->response()->setStatusCode(201);
    }

    public function get(): CartResource
    {
        $user = Auth::user();

        $cart = $this->cartService->get($user);
        $this->handleNotFound($cart->cartItems);

        return new CartResource($cart);
    }

    public function updateItem(CartUpdateRequest $request, int $menuId): CartResource
    {
        $user = Auth::user();
        $data = $request->validated();

        $cart = $this->cartService->updateItem($user, $menuId, $data);
        $this->handleNotFound($cart);

        return new CartResource($cart);
    }

    public function deleteItem(int $menuId): JsonResponse
    {
        $user = Auth::user();

        $status = $this->cartService->deleteItem($user, $menuId);
        if (!$status) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ])->setStatusCode(404));
        }
        return response()->json([
            'data' => true
        ]);
    }
}
