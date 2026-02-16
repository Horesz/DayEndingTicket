<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('munkakorok', function (Blueprint $table) {
            $table->id();
            $table->string('nev'); // pl: Bufé pénztár, Jegy pénztár, Mozi gépész
            $table->string('kod')->unique(); // pl: BUFE_PENZTAR, JEGY_PENZTAR
            $table->foreignId('fiok_id')->constrained('fiokok')->cascadeOnDelete();
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('munkakorok');
    }
};