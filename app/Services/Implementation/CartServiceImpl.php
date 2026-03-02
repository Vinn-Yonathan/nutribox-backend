<?php

namespace App\Services\Implementation;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Log;

class CartServiceImpl implements CartService
{

    // private function recalculateTotalPrice(Cart $cart)
    // {
    //     $cart->load('cartItems.menu');

    //     $total_price = $cart->cartItems->sum(function ($item) {
    //         return $item->quantity * $item->menu->price;
    //     });
    //     $cart->update(['total_price' => $total_price]);
    // }

    function get(User $user): ?Cart
    {
        return $user->cart?->load('cartItems.menu');
    }

    function addItem(User $user, array $menuData): Cart
    {
        $cart = $this->get($user);
        if (!$cart) {
            $cart = Cart::create(["user_id" => $user->id, "total_price" => 0]);
        }

        $cartItem = $cart->cartItems->firstWhere('menu_id', $menuData['menu_id']);
        if ($cartItem) {
            $cartItem->update(['quantity' => $cartItem->quantity + $menuData['quantity']]);
            $cart->updateTotalPrice();
            return $cart;
            // return $this->updateItem($user, $menuData['menu_id'], $menuData);
        }

        $cartItemData = array_merge(
            ["cart_id" => $cart->id],
            $menuData
        );
        // Log::info($cartItemData);

        CartItem::create($cartItemData);
        $cart->updateTotalPrice();
        return $cart;
    }

    function addItemBulk(User $user, array $cartItemsData): Cart
    {
        $cart = $this->get($user);
        if (!$cart) {
            $cart = Cart::create(["user_id" => $user->id, "total_price" => 0]);
        }

        $cartItems = $cart->cartItems;

        $cartItemsData = array_map(function ($item) use ($cart, $cartItems) {
            if ($cartItems?->contains('menu_id', $item['menu_id'])) {
                $cartItem = $cartItems->firstWhere('menu_id', $item['menu_id']);
                $item['quantity'] += $cartItem->quantity;
            }
            return array_merge(['cart_id' => $cart->id], $item);
        }, $cartItemsData);
        Log::info($cartItemsData);

        CartItem::upsert($cartItemsData, ['cart_id', 'menu_id']);
        $cart->updateTotalPrice();
        return $cart;
    }

    function updateItem(User $user, int $menuId, array $menuData): ?Cart
    {
        $cart = $this->get($user);
        if (!$cart) {
            return null;
        }

        $cartItem = $cart->cartItems->firstWhere('menu_id', $menuId);
        if (!$cartItem)
            return null;

        $cartItem->update(['quantity' => $menuData['quantity']]);
        $cart->updateTotalPrice();
        return $cart;
    }

    function deleteItem(User $user, int $menuId): bool
    {
        $cart = $this->get($user);
        if (!$cart) {
            return false;
        }

        $cartItem = $cart->cartItems->firstWhere('menu_id', $menuId);
        if (!$cartItem)
            return false;


        $status = $cartItem->delete();

        if ($status) {
            $cart->updateTotalPrice();
        }

        return $status;
    }

    function delete(User $user): bool
    {
        $cart = $this->get($user);
        if (!$cart) {
            return false;
        }

        return $cart->delete();
    }

}