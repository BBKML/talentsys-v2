<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    protected $table = 'parent';
    public $timestamps = false;
    protected $fillable = [
        'nom', 'prenom', 'sexe', 'contact_1', 'contact_2', 'email', 'lien_parental', 'profession', 'nationalite', 'id_statut', 'id_etablissement'
    ];

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'id_parent');
    }
}
