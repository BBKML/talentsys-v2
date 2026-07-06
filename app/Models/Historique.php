<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historique extends Model
{
    protected $table = 'historique';
    public $timestamps = false;

    protected $fillable = [
        'activite', 'date', 'heure',
        'id_account', 'id_statut', 'id_etablissement',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'id_account');
    }
}
