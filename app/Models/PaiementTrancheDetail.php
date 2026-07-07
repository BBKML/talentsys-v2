<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementTrancheDetail extends Model
{
    use HasFactory;

    protected $table = 'paiement_tranche_detail';

    protected $fillable = [
        'id_paiement',
        'id_tranche_prevu',
        'montant_alloue',
    ];

    public function paiement()
    {
        return $this->belongsTo(Paiement::class, 'id_paiement');
    }

    public function tranchePrevu()
    {
        return $this->belongsTo(TranchePrevu::class, 'id_tranche_prevu');
    }
}
