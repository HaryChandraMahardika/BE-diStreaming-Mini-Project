<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movie_category', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('category_id');

            $table->foreign('movie_id')
                ->references('movie_id')
                ->on('movies')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('category_id')
                ->on('category')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_category');
    }
};