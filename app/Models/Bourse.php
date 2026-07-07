<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bourse extends Model
{
    protected $table = 'bourse';
    protected $fillable = ['libelle', 'type_bourse', 'valeur', 'id_statut', 'id_etablissement'];
    public $timestamps = false;
}
