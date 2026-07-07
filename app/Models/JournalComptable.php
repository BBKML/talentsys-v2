<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalComptable extends Model
{
    protected $table = 'journal_comptable';
    public $timestamps = false;
    protected $fillable = ['code', 'libelle', 'type_journal', 'id_etablissement'];
}
