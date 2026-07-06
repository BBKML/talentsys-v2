<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModalitePaiement extends Model
{
    protected $table = 'modalite_paiement';
    protected $fillable = ['id_frais_scolarite', 'tranche', 'pourcentage', 'date_debut', 'date_fin', 'id_statut', 'id_etablissement'];
    public $timestamps = false;

    public function fraisScolarite() { return $this->belongsTo(FraisScolarite::class, 'id_frais_scolarite'); }
}
