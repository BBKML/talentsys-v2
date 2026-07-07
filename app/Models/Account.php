<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'account';
    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur', 'prenom', 'nom', 'sexe',
        'date_naissance', 'lieu_naissance', 'nationalite',
        'contact', 'url_profil', 'id_statut', 'id_etablissement',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function getInitialsAttribute(): string
    {
        $p = mb_substr($this->prenom ?? '', 0, 1);
        $n = mb_substr($this->nom ?? '', 0, 1);
        return strtoupper("{$p}{$n}");
    }
}
