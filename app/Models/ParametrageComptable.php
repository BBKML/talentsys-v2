<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametrageComptable extends Model
{
    protected $table = 'parametrage_comptable';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    protected $fillable = ['id_etablissement', 'type_operation', 'id_journal', 'id_compte_debit', 'id_compte_credit'];
}
