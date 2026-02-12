<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            // Fotó linkek (Google Photos URL vagy hasonló)
            $table->text('nav_foto_link')->nullable()->after('megjegyzes');
            $table->text('terminal_foto_link')->nullable()->after('nav_foto_link');
        });
    }

    public function down(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->dropColumn(['nav_foto_link', 'terminal_foto_link']);
        });
    }
};