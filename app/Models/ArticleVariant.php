<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleVariant extends Model
{
    protected $table = 'article_variants';
    protected $fillable = ['article_id', 'taille', 'couleur', 'reference'];

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'article_variant_id');
    }
}
