<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionRattrapage extends Model
{
    protected $table = 'session_rattrapage';
    public $timestamps = false;
    protected $fillable = [
        'id_annee_scolaire', 'id_classe', 'id_matiere', 'date_debut', 'date_fin', 'id_statut', 'id_etablissement',
    ];

    public function classe()  { return $this->belongsTo(Classe::class, 'id_classe'); }
    public function matiere() { return $this->belongsTo(Matiere::class, 'id_matiere'); }
}
