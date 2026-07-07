<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $table = 'historique_paiement';

    protected $fillable = [
        'reference',
        'id_inscription',
        'id_frais_scolarite',
        'id_mode_paiement',
        'montant_verse',
        'date',
        'id_statut',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }

    public function fraisScolarite()
    {
        return $this->belongsTo(FraisScolarite::class, 'id_frais_scolarite');
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'id_mode_paiement');
    }

    public function details()
    {
        return $this->hasMany(PaiementTrancheDetail::class, 'id_paiement');
    }
}
