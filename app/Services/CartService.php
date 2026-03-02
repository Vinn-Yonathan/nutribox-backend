<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;

interface CartService
{
    function addItem(User $user, array $menuData): Cart;
    function addItemBulk(User $user, array $cartItemsData): Cart;
    function updateItem(User $user, int $menuId, array $menuData): ?Cart;
    function deleteItem(User $user, int $menuId): ?bool;
    function delete(User $user): ?bool;
    function get(User $user): ?Cart;

}