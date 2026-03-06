<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    private function handleNotFound($data)
    {
        if ($data === null) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'User not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return;
    }

    public function register(UserCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->userService->register($data);

        $userResource = new UserResource($user);
        return $userResource->response()->setStatusCode(201);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->userService->login($data);

        return response()->json([
            'data' => [
                'user' => new UserResource($user['user']),
                'access_token' => $user['access_token']
            ]
        ], 200);
    }

    public function updateCurrent(UserUpdateRequest $request): UserResource
    {
        $user = Auth::user();
        $data = $request->validated();

        $updatedUser = $this->userService->update($user->id, $data);
        return new UserResource($updatedUser);
    }

    public function getCurrent(): UserResource
    {
        $user = $this->userService->get();

        return new UserResource($user);
    }

    public function getById(int $id): UserResource
    {
        $user = $this->userService->getById($id);
        $this->handleNotFound($user);

        return new UserResource($user);
    }

    public function get(Request $request): UserCollection
    {
        $filter = $request->only([
            'name',
            'page',
            'size'
        ]);

        $users = $this->userService->getList($filter);

        return new UserCollection($users);
    }

    public function logout(): JsonResponse
    {
        $this->userService->logout();
        return response()->json([
            'data' => 'true'
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $deleteStatus = $this->userService->delete($id);
        $this->handleNotFound($deleteStatus);

        return response()->json([
            'data' => true
        ]);
    }

    public function update(int $id, UserUpdateRequest $request): UserResource
    {
        $data = $request->validated();

        $updatedUser = $this->userService->update($id, $data);
        $this->handleNotFound($updatedUser);
        return new UserResource($updatedUser);
    }

}
