<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beosztas_kommentek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beosztas_id')
                ->constrained('beosztas') // ← EGYES SZÁM, mert a tábla neve 'beosztas'
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->text('komment');
            $table->enum('tipus', ['megjegyzes', 'csere_keres'])->default('megjegyzes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beosztas_kommentek');
    }
};