<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Couleur extends Model
{
    protected $table = 'couleur';
    public $timestamps = false;
    protected $fillable = ['cle', 'code_hex', 'id_etablissement'];
}
