<?php

namespace App\Providers;

use App\Auth\UtilisateurProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Enregistrement du provider d'auth custom (table utilisateur Supabase)
        Auth::provider('utilisateur', function ($app, array $config) {
            return new UtilisateurProvider();
        });
    }
}
