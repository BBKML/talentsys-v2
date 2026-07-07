<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolumeHoraire extends Model
{
    protected $table = 'volume_horaire';
    public $timestamps = false;
    protected $fillable = [
        'id_affectation_enseignant', 'date_heures_arrive', 'date_heures_depart',
        'id_statut', 'id_etablissement',
    ];

    protected $casts = [
        'date_heures_arrive' => 'datetime',
        'date_heures_depart' => 'datetime',
    ];

    public function affectation()
    {
        return $this->belongsTo(AffectationEnseignant::class, 'id_affectation_enseignant');
    }
}
