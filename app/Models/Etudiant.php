<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $table = 'etudiant';
    public $timestamps = false;
    protected $fillable = [
        'matricule', 'nom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance',
        'nationalite', 'contact', 'email', 'url_photo', 'id_parent', 'id_statut', 'id_etablissement',
    ];

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'id_etudiant');
    }
}
