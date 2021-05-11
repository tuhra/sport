<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ArticleCategory extends Model
{
    protected $fillable = ['title', 'media_id', 'status'];

    public function media()
    {
    	return $this->belongsTo('App\Model\Media');
    }
}
