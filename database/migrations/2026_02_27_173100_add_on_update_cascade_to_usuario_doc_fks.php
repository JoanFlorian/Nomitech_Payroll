<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add ON UPDATE CASCADE to all foreign keys referencing usuario.doc.
 *
 * This allows changing the usuario PK (doc) in the pending payment recovery
 * flow without violating FK constraints. The database engine will automatically
 * propagate the new doc value to all child tables.
 *
 * Affected tables and columns:
 *   - empresa.doc_representante
 *   - usuario_empresa.doc
 *   - usuario_modulo.doc
 *   - contrato.doc
 */
return new class extends Migration {
    public function up(): void
    {
        // 1. empresa.doc_representante → usuario.doc
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropForeign(['doc_representante']);
            $table->foreign('doc_representante')
                ->references('doc')->on('usuario')
                ->onUpdate('cascade');
        });

        // 2. usuario_empresa.doc → usuario.doc
        Schema::table('usuario_empresa', function (Blueprint $table) {
            $table->dropForeign(['doc']);
            $table->foreign('doc')
                ->references('doc')->on('usuario')
                ->onUpdate('cascade');
        });

        // 3. usuario_modulo.doc → usuario.doc (preserve existing onDelete cascade)
        Schema::table('usuario_modulo', function (Blueprint $table) {
            $table->dropForeign(['doc']);
            $table->foreign('doc')
                ->references('doc')->on('usuario')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // 4. contrato.doc → usuario.doc (preserve existing onDelete cascade)
        Schema::table('contrato', function (Blueprint $table) {
            $table->dropForeign(['doc']);
            $table->foreign('doc')
                ->references('doc')->on('usuario')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        // Restore original FKs without ON UPDATE CASCADE

        Schema::table('empresa', function (Blueprint $table) {
            $table->dropForeign(['doc_representante']);
            $table->foreign('doc_representante')
                ->references('doc')->on('usuario');
        });

        Schema::table('usuario_empresa', function (Blueprint $table) {
            $table->dropForeign(['doc']);
            $table->foreign('doc')
                ->references('doc')->on('usuario');
        });

        Schema::table('usuario_modulo', function (Blueprint $table) {
            $table->dropForeign(['doc']);
            $table->foreign('doc')
                ->references('doc')->on('usuario')
                ->onDelete('cascade');
        });

        Schema::table('contrato', function (Blueprint $table) {
            $table->dropForeign(['doc']);
            $table->foreign('doc')
                ->references('doc')->on('usuario')
                ->onDelete('cascade');
        });
    }
};
