<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deliberation extends Model
{
    protected $table = 'deliberation';
    public $timestamps = false;
    protected $fillable = [
        'id_inscription', 'id_decoupage_annee', 'type_deliberation', 'moyenne', 'mention',
        'credits_valides', 'credits_cumules', 'decision', 'url_bulletin', 'observation',
        'id_statut', 'id_etablissement',
    ];

    protected $casts = [
        'moyenne' => 'float',
    ];

    public function inscription() { return $this->belongsTo(Inscription::class, 'id_inscription'); }
}
