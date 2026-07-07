<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcritureComptable extends Model
{
    protected $table = 'ecriture_comptable';
    const UPDATED_AT = null;
    protected $fillable = [
        'id_etablissement', 'id_journal', 'id_annee_scolaire', 'numero_piece',
        'date_ecriture', 'libelle', 'origine', 'id_origine', 'valide',
        'total_debit', 'total_credit', 'created_by', 'id_exercice',
    ];

    protected $casts = [
        'valide' => 'boolean',
    ];

    public function journal() { return $this->belongsTo(JournalComptable::class, 'id_journal'); }
    public function lignes()  { return $this->hasMany(LigneEcriture::class, 'id_ecriture'); }
}
