<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $table = 'salle';
    public $timestamps = false;
    protected $fillable = ['code', 'libelle', 'type', 'id_statut', 'id_etablissement'];
}
