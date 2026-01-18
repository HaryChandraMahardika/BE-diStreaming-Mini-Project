<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    protected $table = 'movies';
    protected $primaryKey = 'movie_id';
    public $timestamps = true;

    protected $appends = ['popularity'];

    protected $fillable = [
        'movie_name', 
        'release_year', 
        'rating',
        'description',
        'poster_url',
        'background_url'
    ];

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class, 'movie_id', 'movie_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class, 
            'movie_category',
            'movie_id',
            'category_id'
        );
    }

    public function getPopularityAttribute()
    {
        if ($this->rating >= 8.5) return 'Top Rated';
        if ($this->rating >= 7.0) return 'Popular';
        return 'Regular';
    }
}