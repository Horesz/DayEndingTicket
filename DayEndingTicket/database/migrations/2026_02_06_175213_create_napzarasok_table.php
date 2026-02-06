<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('napzarasok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Ki töltötte fel
            $table->foreignId('fiok_id')->constrained('fiokok')->onDelete('cascade');
            $table->date('datum');
            
            // Bevételek
            $table->decimal('kartya_bevetel', 10, 2)->default(0);
            $table->decimal('keszpenz_bevetel', 10, 2)->default(0);
            $table->decimal('online_bevetel', 10, 2)->default(0);
            $table->decimal('egyeb_bevetel', 10, 2)->default(0);
            
            // Kiadások
            $table->decimal('napi_ber', 10, 2)->default(0);
            $table->decimal('koltsegek', 10, 2)->default(0);
            
            // Megjegyzések
            $table->text('megjegyzes')->nullable();
            
            // Jóváhagyás
            $table->enum('statusz', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('jovahagyta_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('jovahagyva_at')->nullable();
            $table->text('jovahagyas_megjegyzes')->nullable();
            
            $table->timestamps();
            
            // Egy fiókban egy napon egy napzárás
            $table->unique(['fiok_id', 'datum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('napzarasok');
    }
};