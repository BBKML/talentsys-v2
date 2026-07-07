<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcheancierScolarite extends Model
{
    use HasFactory;

    protected $table = 'echeancier_scolarite';

    protected $fillable = [
        'id_inscription',
        'id_frais_scolarite',
        'montant_total',
        'montant_remise',
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

    public function tranches()
    {
        return $this->hasMany(TranchePrevu::class, 'id_echeancier_scolarite');
    }
}
