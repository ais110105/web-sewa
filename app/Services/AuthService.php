<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function attempt(array $credentials, bool $remember = false): bool
    {
        return Auth::attempt($credentials, $remember);
    }

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $role = $data['role'] ?? 'user';
        unset($data['role']);

        return $this->userRepository->createWithRole($data, $role);
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function user(): ?User
    {
        return Auth::user();
    }
}
