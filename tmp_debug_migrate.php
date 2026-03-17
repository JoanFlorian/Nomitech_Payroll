<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logFile = __DIR__ . '/debug_migrate_error.log';

function logInfo($msg) {
    global $logFile;
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
    echo $msg . "\n";
}

try {
    file_put_contents($logFile, "Starting migration debug...\n");
    
    logInfo("Dropping roles if exists...");
    Schema::dropIfExists('roles');
    
    logInfo("Creating permissions...");
    Schema::create('permissions', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('module');
        $table->string('description')->nullable();
        $table->timestamps();
    });

    logInfo("Creating roles...");
    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('company_id')->index();
        $table->string('name');
        $table->string('description')->nullable();
        $table->timestamps();
        $table->foreign('company_id')->references('id_empresa')->on('empresa')->onDelete('cascade');
    });

    logInfo("Creating role_permissions...");
    Schema::create('role_permissions', function (Blueprint $table) {
        $table->unsignedBigInteger('role_id')->index();
        $table->unsignedBigInteger('permission_id')->index();
        $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        $table->primary(['role_id', 'permission_id']);
    });

    logInfo("Creating user_roles...");
    Schema::create('user_roles', function (Blueprint $table) {
        $table->string('user_id', 20)->index();
        $table->unsignedBigInteger('role_id')->index();
        $table->foreign('user_id')->references('doc')->on('usuario')->onDelete('cascade');
        $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        $table->primary(['user_id', 'role_id']);
    });

    logInfo("Success!");
} catch (\Exception $e) {
    logInfo("ERROR: " . $e->getMessage());
    logInfo($e->getTraceAsString());
}
