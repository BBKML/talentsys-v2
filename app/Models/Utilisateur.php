<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Utilisateur extends Authenticatable
{
    protected $table = 'utilisateur';

    protected $fillable = [
        'mail', 'mot_de_passe', 'id_role', 'id_statut', 'id_etablissement',
    ];

    protected $hidden = ['mot_de_passe'];

    // Désactiver timestamps (la table Supabase n'en a pas forcément)
    public $timestamps = false;

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe ?? '';
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function account()
    {
        return $this->hasOne(Account::class, 'id_utilisateur');
    }
}
