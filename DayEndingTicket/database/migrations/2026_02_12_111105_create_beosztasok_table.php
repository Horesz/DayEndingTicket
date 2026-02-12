<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beosztasok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fiok_id')->constrained('fiokok')->cascadeOnDelete();
            $table->date('datum');
            $table->time('kezdes')->nullable();
            $table->time('befejezes')->nullable();
            $table->text('megjegyzes')->nullable();
            $table->timestamps();

            // Unique: egy dolgozó / nap csak egy beosztás
            $table->unique(['user_id', 'datum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beosztasok');
    }
};