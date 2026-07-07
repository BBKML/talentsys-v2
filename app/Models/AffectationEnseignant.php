<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffectationEnseignant extends Model
{
    protected $table = 'affectation_enseignant';
    public $timestamps = false;
    protected $fillable = [
        'id_enseignant', 'id_annee_scolaire', 'id_classe', 'id_matiere',
        'nombre_heure', 'montant_horaire', 'id_statut', 'id_etablissement',
    ];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'id_enseignant');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'id_matiere');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'id_classe');
    }

    public function annee()
    {
        return $this->belongsTo(AnneeScolaire::class, 'id_annee_scolaire');
    }

    public function volumesHoraires()
    {
        return $this->hasMany(VolumeHoraire::class, 'id_affectation_enseignant');
    }

    public function comptabiliteHoraires()
    {
        return $this->hasMany(ComptabiliteHoraire::class, 'id_affectation_enseignant');
    }

    public function emploiDuTemps()
    {
        return $this->hasMany(EmploiTemps::class, 'id_affectation_enseignant');
    }
}
