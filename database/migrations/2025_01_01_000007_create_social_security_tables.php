<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eps', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('id_eps')->primary();   // 👈 ID correcto (NIT)
            $table->string('nombre', 60)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('afp', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('id_afp')->primary();   // 👈 ID correcto (NIT)
            $table->string('nombre', 60)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('arl', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedBigInteger('id_arl')->primary();   // 👈 ID correcto (NIT)
            $table->string('nombre', 60)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arl');
        Schema::dropIfExists('afp');
        Schema::dropIfExists('eps');
    }
};