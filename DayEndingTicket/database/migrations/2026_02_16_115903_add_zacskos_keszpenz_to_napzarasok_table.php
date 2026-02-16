<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->decimal('zacskos_keszpenz', 10, 2)->default(0)->after('egyeb_bevetel');
        });
    }

    public function down(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->dropColumn('zacskos_keszpenz');
        });
    }
};