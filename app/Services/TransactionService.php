<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionService
{
    function create(array $transactionData, User $user): Transaction;
    function getById(int $transactionId, ?User $user): ?Transaction;
    function getList(?User $user, array $filter): LengthAwarePaginator|Collection;
    function update(array $transactionData): ?Transaction;
    function delete(int $transactionId, ?User $user): ?bool;
}