<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompteComptable extends Model
{
    protected $table = 'plan_comptable';
    public $timestamps = false;
    protected $fillable = [
        'numero_compte', 'libelle', 'classe', 'type_compte', 'sens_normal',
        'actif', 'is_parent', 'id_parent', 'id_etablissement',
    ];

    protected $casts = [
        'actif'     => 'boolean',
        'is_parent' => 'boolean',
    ];
}
