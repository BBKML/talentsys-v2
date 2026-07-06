<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    protected $table = 'etablissement';
    public $timestamps = false;

    protected $fillable = [
        'nom', 'code', 'logo', 'siege', 'systeme_academique',
        'critere_passage_lmd', 'pourcentage_ues_min',
        'contact_1', 'contact_2', 'email_1', 'email_2', 'adresse',
    ];

    protected $casts = [
        'siege'           => 'boolean',
        'pourcentage_ues_min' => 'integer',
    ];

    public function isBTS(): bool
    {
        return strtoupper($this->systeme_academique ?? '') === 'BTS';
    }

    public function isLMD(): bool
    {
        return !$this->isBTS();
    }

    public function couleurs()
    {
        return $this->hasMany(Couleur::class, 'id_etablissement');
    }

    public function getPrimaryColorAttribute(): string
    {
        $couleur = $this->couleurs()->where('cle', 'primary')->first();
        if ($couleur && $couleur->code_hex) {
            return $couleur->code_hex;
        }
        return '#5A67D8';
    }
}
