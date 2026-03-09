<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado', function (Blueprint $table) {
            $table->unsignedBigInteger('id_estado')->autoIncrement(); // ✅
            $table->string('nombre', 60)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('tipo_novedad', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tipo_novedad')->autoIncrement(); // ✅
            $table->string('nombre', 60)->unique();
            $table->timestamps();
        });

       Schema::create('tipo_hora_recargo', function (Blueprint $table) {
       $table->id('id_tipo_hora_recargo');
       $table->string('nombre', 60)->unique();
       $table->decimal('valor', 5, 2)->default(0);
       $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_hora_recargo');
        Schema::dropIfExists('tipo_novedad');
        Schema::dropIfExists('estado');
    }
};