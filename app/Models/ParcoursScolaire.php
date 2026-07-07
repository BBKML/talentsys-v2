<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcoursScolaire extends Model
{
    protected $table = 'parcours_scolaire';
    public $timestamps = false;
    protected $fillable = [
        'id_etudiant', 'etablissement', 'classe', 'annee_scolaire', 'moyenne_generale', 'decision', 'id_etablissement'
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant');
    }
}
