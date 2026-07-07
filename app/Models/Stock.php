<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';
    protected $fillable = ['article_variant_id', 'quantite', 'montant_achat', 'prix_unitaire'];

    protected $casts = [
        'quantite'      => 'integer',
        'montant_achat' => 'float',
        'prix_unitaire' => 'float',
    ];

    public function variant()
    {
        return $this->belongsTo(ArticleVariant::class, 'article_variant_id');
    }
}
