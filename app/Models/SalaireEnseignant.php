<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaireEnseignant extends Model
{
    protected $table = 'salaire_enseignant';
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_enseignant', 'id_annee_scolaire', 'mois', 'salaire_brut',
        'retenue_cnps', 'retenue_ir', 'autres_retenues', 'salaire_net',
        'date_paiement', 'id_mode_paiement', 'statut', 'reference', 'id_etablissement',
    ];

    protected $casts = [
        'salaire_brut'    => 'float',
        'retenue_cnps'    => 'float',
        'retenue_ir'      => 'float',
        'autres_retenues' => 'float',
        'salaire_net'     => 'float',
        'date_paiement'   => 'date',
    ];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'id_enseignant');
    }

    public function annee()
    {
        return $this->belongsTo(AnneeScolaire::class, 'id_annee_scolaire');
    }
}
