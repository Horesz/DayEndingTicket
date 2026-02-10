
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('napzaras_dolgozo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('napzaras_id')->constrained('napzarasok')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('napi_ber', 10, 2)->default(0);
            $table->text('megjegyzes')->nullable();
            $table->timestamps();

            // Egy napzáráshoz egy dolgozó csak egyszer szerepelhet
            $table->unique(['napzaras_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('napzaras_dolgozo');
    }
};