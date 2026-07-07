<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Table "etudiant_bourse" : attribution d'une bourse (table "bourse") à un
 * étudiant pour une période donnée. Référencée par "inscription.id_etudiant_bourse".
 */
class EtudiantBourse extends Model
{
    protected $table = 'etudiant_bourse';

    public $timestamps = false;

    protected $fillable = [
        'id_etudiant',
        'id_bourse',
        'date_debut',
        'date_fin',
        'id_statut',
        'id_etablissement',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant');
    }

    public function bourse()
    {
        return $this->belongsTo(Bourse::class, 'id_bourse');
    }

    public function statut()
    {
        return $this->belongsTo(Statut::class, 'id_statut');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }
}
