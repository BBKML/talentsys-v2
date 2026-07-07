<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentGed extends Model
{
    protected $table = 'document_ged';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'id_dossier', 'nom', 'description', 'categorie', 'url_fichier', 'type_fichier', 'taille_octets', 'uploaded_by'
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierGed::class, 'id_dossier');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
