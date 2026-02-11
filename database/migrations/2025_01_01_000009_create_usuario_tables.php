<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->string('doc', 12)->primary(); // PK sin autoincremento
            $table->integer('id_tipo_doc'); // NOT NULL
            $table->string('contrasena', 255);

            $table->string('primer_nombre', 60);
            $table->string('otros_nombres', 60)->nullable();
            $table->string('primer_apellido', 60);
            $table->string('segundo_apellido', 60)->nullable();

            $table->integer('id_ciudad')->nullable();
            $table->string('direccion', 255);
            $table->string('telefono', 20);
            $table->string('correo', 256);

            $table->integer('id_rol');
            $table->boolean('activo')->default(true);

            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['id_tipo_doc', 'doc']); // UNIQUE KEY

            $table->foreign('id_tipo_doc')->references('id_tipo_doc')->on('tipo_doc');
            $table->foreign('id_ciudad')->references('id_ciudad')->on('ciudad');
            $table->foreign('id_rol')->references('id_rol')->on('rol');
        });

        Schema::create('usuario_modulo', function (Blueprint $table) {
            $table->string('doc', 20);
            $table->integer('id_modulo');

            $table->primary(['doc', 'id_modulo']);

            $table->foreign('doc')->references('doc')->on('usuario')->onDelete('cascade');
            $table->foreign('id_modulo')->references('id_modulo')->on('modulo')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_modulo');
        Schema::dropIfExists('usuario');
    }
};
