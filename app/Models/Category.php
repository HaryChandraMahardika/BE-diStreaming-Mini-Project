<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'movie_category'; 
    protected $primaryKey = 'movie_category_id';
    public $timestamps = false;

    protected $fillable = [
        'category_name'
    ];

    public function movies():HasMany
    {
        return $this->hasMany(Movie::class, 'movie_category_id', 'movie_category_id');
    }

}


