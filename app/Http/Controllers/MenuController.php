<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuAddRequest;
use App\Http\Requests\MenuUpdateRequest;
use App\Http\Resources\MenuCollection;
use App\Http\Resources\MenuResource;
use App\Services\MenuService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class MenuController extends Controller
{
    private MenuService $menuService;
    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    private function handleNotFound($data)
    {
        if ($data === null) {
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

    public function add(MenuAddRequest $request): JsonResponse
    {
        $data = $request->validated();
        $menu = $this->menuService->add($data);

        $menuResource = new MenuResource($menu);

        return $menuResource->response()->setStatusCode(201);
    }

    public function get(Request $request): MenuCollection
    {
        $filter = $request->only([
            'is_featured',
            'name',
            'max_price',
            'min_price',
            'available',
            'min_calories',
            'max_calories',
            'page',
            'size',
        ]);

        $menus = $this->menuService->getList($filter);

        return new MenuCollection($menus);
    }

    public function getById(int $id): MenuResource
    {
        $menu = $this->menuService->getById($id);
        $this->handleNotFound($menu);

        return new MenuResource($menu);
    }
    public function update(int $id, MenuUpdateRequest $menuUpdateRequest): MenuResource
    {
        $data = $menuUpdateRequest->validated();
        Log::info($menuUpdateRequest->all());
        $menu = $this->menuService->update($id, $data);
        $this->handleNotFound($menu);

        return new MenuResource($menu);
    }
    public function delete(int $id): JsonResponse
    {
        $result = $this->menuService->delete($id);
        $this->handleNotFound($result);

        return response()->json([
            'data' => 'true'
        ]);
    }

}
