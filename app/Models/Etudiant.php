<?php

namespace App\Models;

<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> f2976d7518bd4b820700fd38e95290dbd010c0b5
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
<<<<<<< HEAD
    protected $table = 'etudiant';
    public $timestamps = false;
    protected $fillable = [
        'matricule', 'nom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance',
        'nationalite', 'contact', 'email', 'url_photo', 'id_parent', 'id_statut', 'id_etablissement',
    ];

=======
    use HasFactory;

    protected $table = 'etudiant';

    public $timestamps = false;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'contact',
        'email',
        'url_photo',
        'id_parent',
        'id_statut',
        'id_etablissement',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'id_parent');
    }

    public function statut()
    {
        return $this->belongsTo(Statut::class, 'id_statut');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }

    /**
     * Table "inscription" liant un étudiant à un niveau/filière/classe
     * pour une année scolaire donnée (schéma confirmé).
     */
>>>>>>> f2976d7518bd4b820700fd38e95290dbd010c0b5
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'id_etudiant');
    }
<<<<<<< HEAD
=======

    public function inscriptionCourante(?int $anneeScolaireId)
    {
        return $this->inscriptions()
            ->when($anneeScolaireId, fn ($q) => $q->where('id_annee_scolaire', $anneeScolaireId))
            ->latest('id')
            ->first();
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom.' '.$this->prenom);
    }
>>>>>>> f2976d7518bd4b820700fd38e95290dbd010c0b5
}
