<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            if (!Schema::hasColumn('napzarasok', 'dolgozok_json')) {
                $table->json('dolgozok_json')->nullable()->after('napi_ber');
            }
        });
    }

    public function down(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            if (Schema::hasColumn('napzarasok', 'dolgozok_json')) {
                $table->dropColumn('dolgozok_json');
            }
        });
    }
};