<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eps', function (Blueprint $table) {
            $table->integer('id_eps')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('afp', function (Blueprint $table) {
            $table->integer('id_afp')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('arl', function (Blueprint $table) {
            $table->integer('id_arl')->autoIncrement();
            $table->string('nombre', 60)->unique();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arl');
        Schema::dropIfExists('afp');
        Schema::dropIfExists('eps');
    }
};
