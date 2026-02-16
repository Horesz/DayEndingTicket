<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->foreignId('munkakor_id')
                ->nullable()
                ->after('fiok_id')
                ->constrained('munkakorok')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->dropForeign(['munkakor_id']);
            $table->dropColumn('munkakor_id');
        });
    }
};