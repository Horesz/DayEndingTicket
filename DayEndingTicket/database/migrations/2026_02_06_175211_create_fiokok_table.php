<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiokok', function (Blueprint $table) {
            $table->id();
            $table->string('nev');
            $table->string('cim')->nullable();
            $table->string('kod')->unique(); // pl: F001, F002
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiokok');
    }
};