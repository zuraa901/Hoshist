<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('title_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 1-10, validasi di controller
            $table->text('review')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'title_id']); // 1 user 1 rating per judul
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
