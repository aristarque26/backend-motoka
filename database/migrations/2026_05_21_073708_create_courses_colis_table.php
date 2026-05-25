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
        Schema::create('courses_colis', function (Blueprint $table) {
            $table->id('IdCoursesColis');
            $table->foreignId('Idcource')->constrained('courses', 'Idcource');
            $table->foreignId('Idcolis')->constrained('colis', 'Idcolis');
            $table->dateTime('date_transport');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses_colis');
    }
};
