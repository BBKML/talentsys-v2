<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    public $timestamps = false;

    protected $fillable = ['libelle', 'id_etablissement', 'is_super_admin',
        'voir_utilisateurs', 'voir_etablissement', 'voir_abonnements',
        'voir_academique', 'voir_enseignants', 'voir_etudiants',
        'voir_finance', 'voir_evaluations', 'voir_comptabilite',
        'voir_achats', 'voir_ged',
    ];

    protected $casts = [
        'is_super_admin'      => 'boolean',
        'voir_utilisateurs'   => 'boolean',
        'voir_etablissement'  => 'boolean',
        'voir_abonnements'    => 'boolean',
        'voir_academique'     => 'boolean',
        'voir_enseignants'    => 'boolean',
        'voir_etudiants'      => 'boolean',
        'voir_finance'        => 'boolean',
        'voir_evaluations'    => 'boolean',
        'voir_comptabilite'   => 'boolean',
        'voir_achats'         => 'boolean',
        'voir_ged'            => 'boolean',
    ];
}
