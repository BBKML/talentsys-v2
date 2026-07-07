<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditEtudiant extends Model
{
    protected $table = 'credits_etudiant';
    public $timestamps = false;
    protected $fillable = [
        'id_inscription', 'id_ue', 'credits_obtenus', 'valide', 'date_validation', 'id_statut', 'id_etablissement',
    ];

    protected $casts = [
        'valide' => 'boolean',
    ];

    public function inscription() { return $this->belongsTo(Inscription::class, 'id_inscription'); }
    public function ue()          { return $this->belongsTo(Ue::class, 'id_ue'); }
}
