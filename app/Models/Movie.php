<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $table = 'movies';
    protected $primaryKey = 'movie_id';
    public $timestamps = false;

    protected $appends = ['popularity'];


    protected $fillable = [
        'movie_name', 
        'release_year', 
        'rating',
        'movie_category_id'
    ];

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class, 'movie_id', 'movie_id');
    }

    public function categories(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'movie_category_id', 'movie_category_id');
    }


    public function getPopularityAttribute()
    {
        if ($this->rating >= 8.5) {
        return 'Top Rated';
     } elseif ($this->rating >= 7.0) {
        return 'Popular';
     } else {
        return 'Regular';
        }
    }
}


