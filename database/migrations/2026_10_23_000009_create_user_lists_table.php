<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('title_id')->constrained()->cascadeOnDelete();
            // planned = ingin ditonton/dibaca, ongoing = sedang, completed = selesai
            $table->enum('status', ['planned', 'ongoing', 'completed'])->default('planned');
            $table->timestamps();
            $table->unique(['user_id', 'title_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lists');
    }
};
