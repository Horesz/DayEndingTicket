<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('napzarasok', function (Blueprint $table) {
            // Töröld a fiok_id foreign key-t
            try {
                $table->dropForeign(['fiok_id']);
            } catch (\Exception $e) {
                // Skip ha nincs
            }
        });
        
        // Töröld a unique indexet
        DB::statement('ALTER TABLE napzarasok DROP INDEX IF EXISTS napzarasok_fiok_id_datum_unique');
        
        // Add vissza a foreign key-t
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->foreign('fiok_id')->references('id')->on('fiokok')->onDelete('cascade');
        });
        
        // Add hozzá az új unique indexet
        try {
            DB::statement('ALTER TABLE napzarasok ADD UNIQUE napzarasok_unique_munkakor (fiok_id, munkakor_id, datum)');
        } catch (\Exception $e) {
            // Ha már létezik, skip
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE napzarasok DROP INDEX IF EXISTS napzarasok_unique_munkakor');
        
        Schema::table('napzarasok', function (Blueprint $table) {
            $table->unique(['fiok_id', 'datum']);
        });
    }
};