<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{

    protected $table = 'category'; 
    
    protected $primaryKey = 'category_id';

    public $timestamps = true;

    protected $fillable = [
        'category_name'
    ];

    /**
     * RELASI PERBAIKAN: Many-to-Many ke Movie
     * Menghubungkan kategori kembali ke film melalui tabel pivot
     */
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(
            Movie::class,
            'movie_category', 
            'category_id',    
            'movie_id'        
        );
    }
}