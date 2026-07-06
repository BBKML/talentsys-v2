<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Ue extends Model {
    protected $table = 'ue';
    public $timestamps = false;
    protected $fillable = ['libelle', 'id_filiere', 'id_niveau', 'type_ue', 'credit', 'id_statut', 'id_etablissement'];

    public function filiere() { return $this->belongsTo(Filiere::class, 'id_filiere'); }
    public function niveau()  { return $this->belongsTo(Niveau::class,  'id_niveau'); }
}
