<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneEcriture extends Model
{
    protected $table = 'ligne_ecriture';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'id_ecriture', 'id_compte', 'sens', 'montant',
        'libelle_ligne', 'id_tiers', 'type_tiers', 'ordre',
    ];

    protected $casts = [
        'montant' => 'float',
    ];

    public function ecriture() { return $this->belongsTo(EcritureComptable::class, 'id_ecriture'); }
    public function compte()   { return $this->belongsTo(CompteComptable::class, 'id_compte'); }
}
