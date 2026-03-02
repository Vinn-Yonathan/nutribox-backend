<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Pagination\LengthAwarePaginator;


interface MenuService
{
    function add(array $menuData): Menu;
    function getList(array $filter): LengthAwarePaginator;
    function getById(int $id): ?Menu;
    function update(int $id, array $menuData): ?Menu;
    function delete(int $id): ?bool;

}