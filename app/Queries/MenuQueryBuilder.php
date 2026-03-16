<?php

namespace App\Queries;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuQueryBuilder
{
    private Builder $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = Menu::query();
    }

    public function filterByFeatured($isFeatured): self
    {
        if ($isFeatured !== null) {
            $isFeatured = filter_var($isFeatured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isFeatured !== null) {
                $this->queryBuilder->where('is_featured', $isFeatured);
            }
        }

        return $this;
    }
    public function filterByName(?string $name): self
    {
        if ($name) {
            $this->queryBuilder->where('name', 'like', "%{$name}%");
        }
        return $this;
    }
    public function filterByMaxPrice(?int $maxPrice): self
    {
        if ($maxPrice !== null) {
            $this->queryBuilder->where('price', '<=', $maxPrice);
        }
        return $this;
    }
    public function filterByMinPrice(?int $minPrice): self
    {
        if ($minPrice !== null) {
            $this->queryBuilder->where('price', '>=', $minPrice);
        }
        return $this;
    }
    public function filterByMaxCalories(?int $maxCalories): self
    {
        if ($maxCalories !== null) {
            $this->queryBuilder->where('calories', '<=', $maxCalories);
        }
        return $this;
    }
    public function filterByMinCalories(?int $minCalories): self
    {
        if ($minCalories !== null) {
            $this->queryBuilder->where('calories', '>=', $minCalories);
        }
        return $this;
    }
    public function filterByAvailability($available): self
    {
        if ($available !== null) {
            $available = filter_var($available, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($available === false) {
                $this->queryBuilder->where('stock', 0);
            } else if ($available === true) {
                $this->queryBuilder->where('stock', '>', 0);
            }
        }
        return $this;
    }

    public function paginate(?int $perPage, ?int $page): LengthAwarePaginator
    {
        return $this->queryBuilder->orderBy('id', 'asc')->paginate($perPage, page: $page);

    }
}