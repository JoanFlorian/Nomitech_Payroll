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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->string('user_id', 20)->index();
            $table->unsignedBigInteger('permission_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->boolean('active')->default(true);

            $table->foreign('user_id')->references('doc')->on('usuario')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('company_id')->references('id_empresa')->on('empresa')->onDelete('cascade');

            $table->primary(['user_id', 'permission_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
