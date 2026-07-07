<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleType extends Model
{
    protected $table = 'article_types';
    protected $fillable = ['libelle_article_types', 'slug_article_types'];

    public function articles()
    {
        return $this->hasMany(Article::class, 'article_type_id');
    }
}
