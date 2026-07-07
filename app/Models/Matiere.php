<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model {
    protected $table = 'matiere';
    public $timestamps = false;
    protected $fillable = ['libelle', 'coefficient', 'id_ue', 'credit', 'id_decoupage_annee', 'id_filiere', 'id_niveau', 'id_statut', 'id_etablissement'];

    public function ue()       { return $this->belongsTo(Ue::class,            'id_ue'); }
    public function filiere()  { return $this->belongsTo(Filiere::class,        'id_filiere'); }
    public function niveau()   { return $this->belongsTo(Niveau::class,         'id_niveau'); }
    public function decoupage(){ return $this->belongsTo(DecoupageAnnee::class, 'id_decoupage_annee'); }
}
