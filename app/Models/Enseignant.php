<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    protected $table = 'enseignant';
    public $timestamps = false;
    protected $fillable = [
        'matricule', 'nom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance',
        'nationalite', 'email', 'contact_1', 'contact_2', 'grade', 'specialite',
        'url_photo', 'id_statut', 'id_etablissement',
    ];

    public function affectations()
    {
        return $this->hasMany(AffectationEnseignant::class, 'id_enseignant');
    }

    public function salaires()
    {
        return $this->hasMany(SalaireEnseignant::class, 'id_enseignant');
    }
}
