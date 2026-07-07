<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    protected $table = 'inscription';
    public $timestamps = false;

    protected $fillable = [
        'numero_inscription',
        'date_inscription',
        'id_etudiant',
        'id_etudiant_bourse',
        'id_annee_scolaire',
        'id_niveau',
        'id_filiere',
        'id_classe',
        'affecte',
        'type_inscription',
        'id_statut',
        'id_etablissement',
    ];

    protected $casts = [
        'date_inscription' => 'date',
        'affecte' => 'boolean',
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant');
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'id_niveau');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'id_filiere');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'id_classe');
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'id_annee_scolaire');
    }

    public function statut()
    {
        return $this->belongsTo(Statut::class, 'id_statut');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }

    public function etudiantBourse()
    {
        return $this->belongsTo(EtudiantBourse::class, 'id_etudiant_bourse');
    }
}
