<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'articles';
    protected $fillable = ['libelle', 'slug', 'description', 'prix_unitaire', 'article_type_id', 'inclus_scolarite'];

    protected $casts = [
        'prix_unitaire'    => 'float',
        'inclus_scolarite' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(ArticleType::class, 'article_type_id');
    }

    public function variants()
    {
        return $this->hasMany(ArticleVariant::class, 'article_id');
    }
}
