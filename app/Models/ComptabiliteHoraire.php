<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComptabiliteHoraire extends Model
{
    protected $table = 'comptabilite_horaire';
    public $timestamps = false;
    protected $fillable = [
        'id_affectation_enseignant', 'heures_realisees', 'montant_total',
        'date', 'id_statut', 'id_etablissement',
    ];

    protected $casts = [
        'heures_realisees' => 'float',
        'montant_total'    => 'float',
        'date'             => 'date',
    ];

    public function affectation()
    {
        return $this->belongsTo(AffectationEnseignant::class, 'id_affectation_enseignant');
    }
}
