<?php

namespace App\Queries;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionQueryBuilder
{
    private Builder $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = Transaction::query()->with('transactionItems.menus');
    }

    public function filterByUser(?int $userId): self
    {
        if ($userId !== null) {
            $this->queryBuilder->where('user_id', $userId);
        }
        return $this;
    }
    public function filterByMinPrice(?int $minPrice): self
    {
        if ($minPrice !== null) {
            $this->queryBuilder->where('price', ">=", $minPrice);
        }
        return $this;
    }
    public function filterByMaxPrice(?int $maxPrice): self
    {
        if ($maxPrice !== null) {
            $this->queryBuilder->where('price', "<=", $maxPrice);
        }
        return $this;
    }
    public function filterIncludeDeleted(?bool $includeDeleted): self
    {
        if ($includeDeleted !== null) {
            $includeDeleted ? $this->queryBuilder->whereNotNull('deleted_at') : $this->queryBuilder->whereNull("deleted_at");
        }
        return $this;
    }

    public function filterByStatus(?string $status): self
    {
        if ($status !== null) {
            $this->queryBuilder->where('status', $status);
        }
        return $this;
    }
    public function filterByPaymentMethod(?string $paymentMethod): self
    {
        if ($paymentMethod !== null) {
            $this->queryBuilder->where('payment_method', $paymentMethod);
        }
        return $this;
    }

    public function paginate(?int $perPage, ?int $page): LengthAwarePaginator
    {
        return $this->queryBuilder->orderBy('id', 'asc')->paginate($perPage, page: $page);

    }
}