<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeNote extends Model
{
    protected $table = 'type_note';
    public $timestamps = false;
    protected $fillable = ['libelle', 'type_systeme', 'pourcentage', 'id_statut', 'id_etablissement'];
}
