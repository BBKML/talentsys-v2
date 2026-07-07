<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $table = 'facture';

    protected $fillable = [
        'numero_facture',
        'id_inscription',
        'id_frais_scolarite',
        'montant_total',
        'date_facture',
        'statut_facture',
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
}
