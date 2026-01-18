<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    // 1. Arahkan ke tabel master category, bukan tabel pivot
    protected $table = 'category'; 
    
    // 2. Sesuaikan Primary Key
    protected $primaryKey = 'category_id';

    // 3. Set ke true karena di DB Anda ada created_at & updated_at
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
            'movie_category', // Nama tabel pivot
            'category_id',    // Foreign key di tabel pivot untuk Category
            'movie_id'        // Foreign key di tabel pivot untuk Movie
        );
    }
}