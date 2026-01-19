<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist', function (Blueprint $table) {
            $table->id('watchlist_id');

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('movie_id');

            // Status watchlist (contoh: planned, watching, completed)
            $table->string('status')->default('planned');

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('movie_id')
                ->references('movie_id')
                ->on('movies')
                ->onDelete('cascade');

            // Optional: cegah movie yang sama ditambahkan dua kali oleh user yang sama
            $table->unique(['user_id', 'movie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist');
    }
};
