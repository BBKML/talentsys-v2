<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationComptable extends Model
{
    protected $table = 'operation_comptable';
    public $timestamps = false;
    protected $fillable = [
        'id_annee_scolaire', 'id_decoupage_annee', 'id_categorie_comptable', 'id_direction',
        'libelle', 'montant', 'date', 'type_operation', 'url_justificatif',
        'id_historique_paiement', 'id_statut', 'id_etablissement', 'origine',
    ];

    protected $casts = [
        'montant' => 'float',
    ];

    public function categorie() { return $this->belongsTo(CategorieComptable::class, 'id_categorie_comptable'); }
}
