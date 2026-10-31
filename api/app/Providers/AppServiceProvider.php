<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Runs before every authorization check. true = allow; null = "no opinion",
        // so policies still get their say. Never return false here, or you'd
        // veto policies that would have allowed the action.
        Gate::before(function (User $user, string $ability) {
            return $user->hasPermission($ability) ?: null;
        });

        // The database stores "user"/"role", not PHP class names, so refactoring namespaces can't orphan old rows.
        // "enforce" = a model without an alias throws, instead of silently storing its class name.
        Relation::enforceMorphMap([
            'user' => User::class,
            'role' => Role::class,
        ]);
    }
}
