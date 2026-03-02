<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserService
{
    function register(array $userData): User;
    function login(array $userData): array;
    function update(int $id, array $userData): ?User;
    function logout(): bool;
    function delete(int $id): ?bool;
    function get(): User;
    function getById(int $id): ?User;
    function getList(array $filter): LengthAwarePaginator;
}