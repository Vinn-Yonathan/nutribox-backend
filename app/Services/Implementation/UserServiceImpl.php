<?php

namespace App\Services\Implementation;

use App\Models\User;
use App\Queries\UserQueryBuilder;
use App\Services\UserService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserServiceImpl implements UserService
{
    function register(array $userData): User
    {
        $userData['password'] = Hash::make($userData['password']);
        return User::create($userData);

    }
    function login(array $userData): array
    {
        $user = User::where('email', $userData['email'])->first();
        if (!$user || !Hash::check($userData['password'], $user->password)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['email or password wrong']
                ]
            ], 401));
        }
        return [
            'user' => $user,
            'access_token' => $user->createToken('auth-token')->plainTextToken
        ];
    }
    function update(int $id, array $userData): ?User
    {
        $user = User::find($id);
        if ($user) {
            if (isset($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            }
            $user->update($userData);
            return $user->fresh();
        }

        return null;
    }

    function logout(): bool
    {
        if (Auth::user()->currentAccessToken()->delete()) {
            return true;
        }
        return false;
    }

    function get(): User
    {
        return Auth::user();
    }

    function getById(int $id): ?User
    {
        return User::find($id);
    }

    function getList($filter): LengthAwarePaginator
    {
        return (new UserQueryBuilder())
            ->filterByName($filter['name'] ?? null)
            ->paginate(page: $filter['page'] ?? 1, perPage: $filter['size'] ?? 10);
    }

    function delete(int $id): ?bool
    {
        $user = User::find($id);

        if ($user) {
            return $user->delete();
        }

        return null;
    }

}