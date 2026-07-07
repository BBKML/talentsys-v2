<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierGed extends Model
{
    protected $table = 'dossier_ged';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'nom', 'id_parent', 'categorie', 'description'
    ];

    public function parent()
    {
        return $this->belongsTo(DossierGed::class, 'id_parent');
    }

    public function children()
    {
        return $this->hasMany(DossierGed::class, 'id_parent');
    }

    public function documents()
    {
        return $this->hasMany(DocumentGed::class, 'id_dossier');
    }
}
