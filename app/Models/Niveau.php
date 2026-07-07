<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Niveau extends Model {
    protected $table = 'niveau';
    public $timestamps = false;
    protected $fillable = ['code', 'libelle', 'type_niveau', 'credits_requis', 'ordre', 'id_statut', 'id_etablissement'];
}
