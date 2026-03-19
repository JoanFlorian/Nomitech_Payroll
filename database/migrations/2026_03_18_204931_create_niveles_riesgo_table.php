<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('niveles_riesgo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nivel I, II, III...
            $table->decimal('porcentaje', 8, 5); // Ej: 0.00522
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles_riesgo');
    }
};