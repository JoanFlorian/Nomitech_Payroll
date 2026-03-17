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
        Schema::dropIfExists('audit_logs');
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('user_id', 20)->index();
            $table->string('action');
            $table->string('module');
            $table->string('entity_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('company_id')->references('id_empresa')->on('empresa')->onDelete('cascade');
            $table->foreign('user_id')->references('doc')->on('usuario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
