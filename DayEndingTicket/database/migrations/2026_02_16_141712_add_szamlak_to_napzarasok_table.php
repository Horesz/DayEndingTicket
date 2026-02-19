<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->decimal('kimeno_szamla', 10, 2)->default(0)->after('zacskos_keszpenz');
            $table->decimal('bejovo_szamla', 10, 2)->default(0)->after('kimeno_szamla');
        });
    }

    public function down(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->dropColumn(['kimeno_szamla', 'bejovo_szamla']);
        });
    }
};