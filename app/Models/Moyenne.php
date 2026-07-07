<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moyenne extends Model
{
    protected $table = 'moyenne';
    public $timestamps = false;
    protected $fillable = [
        'id_inscription', 'id_matiere', 'id_decoupage_annee', 'moyenne',
        'mention', 'credits_acquis', 'id_statut', 'id_etablissement',
    ];

    protected $casts = [
        'moyenne' => 'float',
    ];

    public function inscription() { return $this->belongsTo(Inscription::class, 'id_inscription'); }
    public function matiere()     { return $this->belongsTo(Matiere::class, 'id_matiere'); }
}
