<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtudiantBourse extends Model
{
    protected $table = 'etudiant_bourse';
    public $timestamps = false;
    protected $fillable = [
        'id_etudiant', 'id_bourse', 'id_annee_scolaire', 'id_etablissement'
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant');
    }

    public function bourse()
    {
        return $this->belongsTo(Bourse::class, 'id_bourse');
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'id_annee_scolaire');
    }
}
