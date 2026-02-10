<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('fizetes_tipus', ['napi', 'allando'])->default('allando')->after('fiok_id');
            // opcionális: alapbér mező az állandó fizetésűekhez
            $table->decimal('alapber_havi', 10, 2)->nullable()->after('fizetes_tipus');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fizetes_tipus', 'alapber_havi']);
        });
    }
};