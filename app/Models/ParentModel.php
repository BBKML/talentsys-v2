<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    protected $table = 'parent';
    public $timestamps = false;
    protected $fillable = [
        'nom', 'prenom', 'sexe', 'contact_1', 'contact_2', 'email', 'lien_parental', 'profession', 'nationalite', 'id_statut', 'id_etablissement'
    ];

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'id_parent');
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * NB: la classe ne peut pas s'appeler "Parent" car c'est un mot réservé en PHP.
 * On la nomme donc ParentModel tout en la faisant pointer vers la table "parent".
 */
class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parent';

    public $timestamps = false; // pas de created_at / updated_at dans le schéma fourni

    protected $fillable = [
        'nom',
        'prenom',
        'sexe',
        'contact_1',
        'contact_2',
        'email',
        'lien_parental',
        'profession',
        'nationalite',
        'id_statut',
        'id_etablissement',
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }

    public function statut()
    {
        return $this->belongsTo(Statut::class, 'id_statut');
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom.' '.$this->prenom);
>>>>>>> f2976d7518bd4b820700fd38e95290dbd010c0b5
    }
}
