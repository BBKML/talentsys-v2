<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonReception extends Model
{
    protected $table = 'bon_reception';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'id_bon_commande', 'date_reception', 'notes'
    ];

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class, 'id_bon_commande');
    }
}
