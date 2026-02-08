<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan', function (Blueprint $table) {
            $table->id(); // BigInteger id
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('valor', 10, 2);
            $table->integer('num_empl');
            $table->integer('duracion'); // Days? Months? Assuming integer for now as per prompt
            $table->string('stripe_price_id')->nullable(); // Stripe Price ID
            $table->timestamps();
        });

        Schema::create('licencia', function (Blueprint $table) {
            $table->id();
            $table->integer('empresa_id'); // FK to empresa.id_empresa (which is integer)
            $table->foreignId('plan_id')->constrained('plan');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->timestamps();

            // Foreign Key to Empresa
            // Note: Empresa table PK is id_empresa (integer)
            $table->foreign('empresa_id')->references('id_empresa')->on('empresa')->onDelete('cascade');
        });

        Schema::create('pago', function (Blueprint $table) {
            $table->id();
            $table->integer('empresa_id');
            $table->foreignId('licencia_id')->constrained('licencia');

            $table->string('referencia')->nullable()->unique(); // Internal reference if needed
            $table->string('proveedor_pago')->default('STRIPE');

            $table->decimal('valor', 10, 2);
            $table->string('moneda', 3)->default('COP'); // or COP
            $table->string('estado_pago')->default('pending'); // pending, paid, failed, expired

            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_session_id')->nullable(); // Stripe Checkout Session ID
            $table->timestamp('fecha_pago')->nullable();

            $table->timestamps();

            $table->foreign('empresa_id')->references('id_empresa')->on('empresa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago');
        Schema::dropIfExists('licencia');
        Schema::dropIfExists('plan');
    }
};
