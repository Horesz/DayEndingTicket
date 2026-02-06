<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beosztas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fiok_id')->constrained('fiokok')->onDelete('cascade');
            $table->date('datum');
            $table->time('kezdes')->nullable();
            $table->time('befejezes')->nullable();
            $table->text('megjegyzes')->nullable();
            $table->timestamps();
            
            // Egy dolgozó egy napon egy helyen dolgozik
            $table->unique(['user_id', 'datum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beosztas');
    }
};