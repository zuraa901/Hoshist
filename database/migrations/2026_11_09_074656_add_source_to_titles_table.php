<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titles', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('poster');
            $table->string('external_id', 30)->nullable()->after('source');
            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('titles', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_id']);
            $table->dropColumn(['source', 'external_id']);
        });
    }
};