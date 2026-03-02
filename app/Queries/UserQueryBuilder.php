<?php

namespace App\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class UserQueryBuilder
{
    private Builder $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = User::query();
    }

    public function filterByName(?string $name): self
    {
        if ($name) {
            $this->queryBuilder->where(function ($query) use ($name) {
                $query
                    ->where('first_name', 'like', "%{$name}%")
                    ->orWhere('last_name', 'like', "%{$name}%");
            });
        }

        return $this;
    }

    public function paginate(?int $page, ?int $perPage): LengthAwarePaginator
    {
        return $this->queryBuilder->paginate(perPage: $perPage, page: $page);
    }
}