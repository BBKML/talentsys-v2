<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnneeScolaire extends Model
{
    protected $table = 'annee_scolaire';
    public $timestamps = false;
    protected $fillable = ['libelle', 'date_debut', 'date_fin', 'active', 'id_statut', 'id_etablissement'];
    protected $casts = ['active' => 'boolean'];
}
