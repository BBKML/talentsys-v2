<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    protected $table = 'dossier';
    public $timestamps = false;
    protected $fillable = [
        'id_inscription', 'id_type_document', 'url_document', 'date_ajout', 'id_statut', 'id_etablissement', 'id_etudiant'
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant');
    }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class, 'id_inscription');
    }

    public function typeDocument()
    {
        return $this->belongsTo(TypeDocument::class, 'id_type_document');
    }

    public function status()
    {
        return $this->belongsTo(Statut::class, 'id_statut');
    }
}
