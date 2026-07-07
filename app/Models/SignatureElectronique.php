<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureElectronique extends Model
{
    protected $table = 'signature_electronique';
    public $timestamps = false;
    protected $fillable = [
        'id_etablissement', 'nom', 'fonction', 'url_signature', 'actif'
    ];
}
