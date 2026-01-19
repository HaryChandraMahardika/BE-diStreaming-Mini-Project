<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id('movie_id'); 
            $table->string('movie_name');
            $table->year('release_year');
            $table->decimal('rating', 3, 1); 
            $table->text('description')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('background_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};