<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    protected $table = 'direction';
    public $timestamps = false;

    protected $fillable = [
        'id_utilisateur', 'id_account', 'id_etablissement',
        'id_role', 'id_statut',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'id_account');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }
}
