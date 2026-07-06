<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraisScolarite extends Model
{
    protected $table = 'frais_scolarite';
    protected $fillable = ['id_niveau', 'id_annee_scolaire', 'id_type_frais', 'montant', 'id_statut', 'id_etablissement'];
    public $timestamps = false;

    public function niveau() { return $this->belongsTo(Niveau::class, 'id_niveau'); }
    public function annee()  { return $this->belongsTo(AnneeScolaire::class, 'id_annee_scolaire'); }
    public function typeFrais() { return $this->belongsTo(TypeFrais::class, 'id_type_frais'); }
}
