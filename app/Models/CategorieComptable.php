<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieComptable extends Model
{
    protected $table = 'categorie_comptable';
    public $timestamps = false;
    protected $fillable = ['code', 'libelle', 'type_categorie', 'id_statut', 'id_etablissement'];
}
