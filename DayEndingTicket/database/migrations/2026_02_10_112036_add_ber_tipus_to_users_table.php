<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('ber_tipus', ['napi', 'fix'])
                ->default('napi')
                ->after('fiok_id');

            $table->decimal('alap_ber', 10, 2)
                ->nullable()
                ->after('ber_tipus');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ber_tipus', 'alap_ber']);
        });
    }
};
