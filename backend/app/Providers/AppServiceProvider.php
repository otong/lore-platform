<?php

namespace App\Providers;

use App\Modules\Knowledge\Domain\Contracts\KnowledgeRepositoryInterface;
use App\Modules\Knowledge\Infrastructure\Persistence\Repositories\KnowledgeRepository;
use App\Modules\Organization\Domain\Contracts\OrganizationRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Repositories\OrganizationRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
        $this->app->bind(KnowledgeRepositoryInterface::class, KnowledgeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
