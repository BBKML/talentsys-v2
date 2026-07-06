<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DecoupageAnnee extends Model {
    protected $table = 'decoupage_annee';
    public $timestamps = false;
    protected $fillable = ['id_annee_scolaire', 'libelle', 'type', 'ordre', 'date_debut', 'date_fin', 'id_statut', 'id_etablissement'];

    public function annee() {
        return $this->belongsTo(AnneeScolaire::class, 'id_annee_scolaire');
    }
}
