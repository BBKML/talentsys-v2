<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactureFournisseur extends Model
{
    protected $table = 'facture_fournisseur';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'id_bon_commande', 'id_fournisseur', 'numero_facture', 'montant', 'date_facture', 'date_echeance', 'statut', 'notes', 'url_document'
    ];

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class, 'id_bon_commande');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'id_fournisseur');
    }
}
