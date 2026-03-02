<?php

namespace App\Services\Implementation;

use App\Models\Menu;
use App\Queries\MenuQueryBuilder;
use App\Services\MenuService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MenuServiceImpl implements MenuService
{

    function add(array $menuData): Menu
    {
        if (isset($menuData['image']) && $menuData['image'] instanceof UploadedFile) {
            $path = $menuData['image']->store('menus', 'public');
            $menuData['image_src'] = '/storage/' . $path;
            unset($menuData['image']);
        }

        return Menu::create($menuData);

        // kedepan perlu pake transaction biargk orphan file (file disimpan tapi error command DB)
    }

    function getList(array $filter): LengthAwarePaginator
    {
        return (new menuQueryBuilder())
            ->filterByFeatured($filter['is_featured'] ?? null)
            ->filterByName($filter['name'] ?? null)
            ->filterByMinPrice($filter['min_price'] ?? null)
            ->filterByMaxPrice($filter['max_price'] ?? null)
            ->filterByAvailability($filter['available'] ?? null)
            ->filterByMinCalories($filter['min_calories'] ?? null)
            ->filterByMaxCalories($filter['max_calories'] ?? null)
            ->paginate(perPage: $filter['size'] ?? 10, page: $filter['page'] ?? 1);

    }
    function getById(int $id): ?Menu
    {
        return Menu::find($id);
    }
    function update(int $id, array $menuData): ?Menu
    {
        $menu = Menu::find($id);
        if (!$menu)
            return null;

        $oldPath = $menu->image_src ? str_replace('/storage/', '', $menu->image_src) : null;
        $newPath = null;

        if (isset($menuData['image']) && $menuData['image'] instanceof UploadedFile) {
            $newPath = $menuData['image']->store('menus', 'public');
            $menuData['image_src'] = '/storage/' . $newPath;
            unset($menuData['image']);

        }

        $menu->update($menuData);

        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
        return $menu->fresh();
    }

    function delete(int $id): ?bool
    {
        $menu = Menu::find($id);
        if (!$menu)
            return null;
        return $menu->delete();
    }
}