<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statut extends Model
{
    protected $table = 'statut';
    public $timestamps = false;
    protected $fillable = ['libelle'];
}
