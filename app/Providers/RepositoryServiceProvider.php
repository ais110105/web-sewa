<?php

namespace App\Providers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ItemRepositoryInterface;
use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\CategoryServiceInterface;
use App\Contracts\Services\ItemServiceInterface;
use App\Contracts\Services\ProfileServiceInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\ItemRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CategoryService;
use App\Services\ItemService;
use App\Services\ProfileService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);

        // Register Services
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(ItemServiceInterface::class, ItemService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
