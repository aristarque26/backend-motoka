<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('passagers', function (Blueprint $table) {
            $table->id('Idpassager');
            $table->string('nomPassager');
            $table->string('telephone')->nullable();
            $table->integer('nombre_sieges')->default(1);
            $table->decimal('tarif_paye', 15, 2);
            $table->string('devise', 5)->default('CDF');
            $table->foreignId('Idcource')->constrained('courses', 'Idcource')->onDelete('cascade');
            $table->foreignId('Idagence')->constrained('agences', 'Idagence')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passagers');
    }
};
