<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonCommande extends Model
{
    protected $table = 'bon_commande';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'id_fournisseur', 'numero', 'date_commande', 'statut', 'notes', 'total'
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'id_fournisseur');
    }

    public function lignes()
    {
        return $this->hasMany(LigneCommande::class, 'id_bon_commande');
    }
}
