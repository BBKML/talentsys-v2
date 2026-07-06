<?php

namespace App\Auth;

use App\Models\Utilisateur;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Provider d'authentification custom pour la table `utilisateur`.
 * Les mots de passe sont stockés en clair dans Supabase (héritage Flutter).
 */
class UtilisateurProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        return Utilisateur::find($identifier);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return Utilisateur::where('id', $identifier)
            ->where('remember_token', $token)
            ->first();
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Pas de colonne remember_token dans la table Supabase
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return Utilisateur::where('mail', strtolower(trim($credentials['mail'] ?? '')))
            ->where('id_statut', 1)
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // Comparaison directe (mots de passe en clair dans Supabase)
        return $user->getAuthPassword() === ($credentials['password'] ?? '');
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Rien — on ne rehashe pas pour ne pas casser l'app Flutter existante
    }
}
