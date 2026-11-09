<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->text('bio')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('bio');
            $table->enum('role', ['user', 'admin'])->default('user')->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'bio', 'avatar', 'role']);
        });
    }
};
