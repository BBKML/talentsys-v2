<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploiTemps extends Model
{
    protected $table = 'emploi_temps';
    public $timestamps = false;
    protected $fillable = [
        'id_affectation_enseignant', 'id_salle', 'date_heure_debut',
        'date_heure_fin', 'motif_modification', 'id_statut', 'id_etablissement',
    ];

    protected $casts = [
        'date_heure_debut' => 'datetime',
        'date_heure_fin'   => 'datetime',
    ];

    public function affectation()
    {
        return $this->belongsTo(AffectationEnseignant::class, 'id_affectation_enseignant');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'id_salle');
    }
}
