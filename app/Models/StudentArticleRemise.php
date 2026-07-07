<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentArticleRemise extends Model
{
    protected $table = 'student_articles_remise';
    protected $fillable = ['inscription_student_id', 'article_variant_id', 'quantite', 'prix_unitaire', 'montant', 'statut_paiement'];

    protected $casts = [
        'quantite'      => 'integer',
        'prix_unitaire' => 'float',
        'montant'       => 'float',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class, 'inscription_student_id');
    }

    public function variant()
    {
        return $this->belongsTo(ArticleVariant::class, 'article_variant_id');
    }
}
