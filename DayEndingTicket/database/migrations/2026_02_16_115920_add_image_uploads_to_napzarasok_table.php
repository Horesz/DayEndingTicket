<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->string('nav_kep_path')->nullable()->after('nav_foto_link');
            $table->string('terminal_kep_path')->nullable()->after('terminal_foto_link');
        });
    }

    public function down(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->dropColumn(['nav_kep_path', 'terminal_kep_path']);
        });
    }
};