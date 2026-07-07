<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranchePrevu extends Model
{
    use HasFactory;

    protected $table = 'tranche_prevu';

    protected $fillable = [
        'id_echeancier_scolarite',
        'id_modalite_paiement',
        'montant',
        'date_echeance',
        'statut_paiement',
        'id_statut',
    ];

    public function echeancier()
    {
        return $this->belongsTo(EcheancierScolarite::class, 'id_echeancier_scolarite');
    }

    public function modalitePaiement()
    {
        return $this->belongsTo(ModalitePaiement::class, 'id_modalite_paiement');
    }

    public function detailsPaiement()
    {
        return $this->hasMany(PaiementTrancheDetail::class, 'id_tranche_prevu');
    }
}
