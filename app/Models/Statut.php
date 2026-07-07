<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statut extends Model
{
    use HasFactory;

    protected $table = 'statut';

    public $timestamps = false; // pas de created_at / updated_at dans le schéma fourni

    protected $fillable = [
        'libelle',
    ];

    /**
     * Relations inverses possibles (décommente/adapte selon tes autres modèles
     * qui référencent id_statut, ex: ParentModel, Niveau, etc.)
     */
    public function parents()
    {
        return $this->hasMany(ParentModel::class, 'id_statut');
    }
}
