<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getPaginatedUsers(int $perPage = 10): LengthAwarePaginator
    {
        return User::with('roles')->latest()->paginate($perPage);
    }

    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $role = $data['role'] ?? 'user';
        unset($data['role']);

        return $this->userRepository->createWithRole($data, $role);
    }

    public function updateUser(int $id, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $role = $data['role'] ?? null;
        unset($data['role']);

        $result = $this->userRepository->update($id, $data);

        if ($result && $role) {
            $user = $this->userRepository->find($id);
            $this->assignRole($user, $role);
        }

        return $result;
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function findUser(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function assignRole(User $user, string $role): void
    {
        $user->syncRoles([$role]);
    }
}
