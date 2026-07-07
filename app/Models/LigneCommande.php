<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneCommande extends Model
{
    protected $table = 'ligne_commande';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'id_bon_commande', 'designation', 'quantite', 'prix_unitaire', 'montant'
    ];

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class, 'id_bon_commande');
    }
}
