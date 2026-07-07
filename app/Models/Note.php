<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notes';
    public $timestamps = false;
    protected $fillable = [
        'id_inscription', 'id_matiere', 'id_type_note', 'note', 'note_modifiee',
        'session', 'id_utilisateur', 'id_statut', 'id_etablissement',
    ];

    protected $casts = [
        'note' => 'float',
    ];

    public function inscription() { return $this->belongsTo(Inscription::class, 'id_inscription'); }
    public function matiere()     { return $this->belongsTo(Matiere::class, 'id_matiere'); }
    public function typeNote()    { return $this->belongsTo(TypeNote::class, 'id_type_note'); }
}
