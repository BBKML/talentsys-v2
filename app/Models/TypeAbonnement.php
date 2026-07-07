<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeAbonnement extends Model
{
    protected $table = 'type_abonnement';
    public $timestamps = false;
    protected $fillable = ['libelle', 'nb_utilisateurs_max', 'nb_etudiants_max', 'prix_mensuel', 'id_statut', 'id_etablissement'];

    public function status()
    {
        return $this->belongsTo(Statut::class, 'id_statut');
    }
}
