<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model {
    protected $table = 'classe';
    public $timestamps = false;
    protected $fillable = ['id_annee_scolaire', 'id_filiere', 'id_niveau', 'libelle', 'effectif', 'capacite_max', 'id_statut', 'id_etablissement'];

    public function filiere()  { return $this->belongsTo(Filiere::class, 'id_filiere'); }
    public function niveau()   { return $this->belongsTo(Niveau::class,  'id_niveau'); }
    public function annee()    { return $this->belongsTo(AnneeScolaire::class, 'id_annee_scolaire'); }
}
