<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeDocument extends Model
{
    protected $table = 'type_document';
    public $timestamps = false;
    protected $fillable = ['libelle', 'obligatoire', 'id_statut', 'id_etablissement'];

    public function status()
    {
        return $this->belongsTo(Statut::class, 'id_statut');
    }
}
