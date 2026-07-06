<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model {
    protected $table = 'filiere';
    public $timestamps = false;
    protected $fillable = ['libelle', 'id_statut', 'id_etablissement'];
}
