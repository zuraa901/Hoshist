<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titles', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->enum('type', ['anime', 'film', 'drama', 'manga', 'komik'])->index();
            $table->text('synopsis')->nullable();
            $table->string('poster')->nullable();
            $table->year('release_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titles');
    }
};
