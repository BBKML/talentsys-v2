<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeFrais extends Model
{
    protected $table = 'type_frais';
    protected $fillable = ['libelle', 'obligatoire', 'id_statut', 'id_etablissement'];
    public $timestamps = false;
}
