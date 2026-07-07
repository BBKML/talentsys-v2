<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $table = 'abonnement';
    public $timestamps = false;
    protected $fillable = ['id_type_abonnement', 'date_debut', 'date_fin', 'id_statut', 'id_etablissement'];

    public function type()
    {
        return $this->belongsTo(TypeAbonnement::class, 'id_type_abonnement');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }

    public function status()
    {
        return $this->belongsTo(Statut::class, 'id_statut');
    }
}
