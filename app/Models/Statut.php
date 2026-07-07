<?php

namespace App\Models;

<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> f2976d7518bd4b820700fd38e95290dbd010c0b5
use Illuminate\Database\Eloquent\Model;

class Statut extends Model
{
<<<<<<< HEAD
    protected $table = 'statut';
    public $timestamps = false;
    protected $fillable = ['libelle'];
=======
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
>>>>>>> f2976d7518bd4b820700fd38e95290dbd010c0b5
}
