<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $table = 'fournisseur';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'nom', 'numero_contribuable', 'adresse', 'telephone', 'email', 'id_compte_charge', 'actif'
    ];

    public function compte()
    {
        return $this->belongsTo(CompteComptable::class, 'id_compte_charge');
    }
}
