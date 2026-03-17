<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create the link between legacy roles and modern permissions
        Schema::create('rol_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('permission_id');

            $table->foreign('id_rol')->references('id_rol')->on('rol')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->primary(['id_rol', 'permission_id']);
        });

        // 2. Data Migration: Migrate permissions from new 'roles' back to 'rol' (matching by name)
        if (Schema::hasTable('role_permissions') && Schema::hasTable('roles')) {
            $roles = DB::table('roles')->get();
            foreach ($roles as $newRole) {
                $legacyRolId = DB::table('rol')->where('nombre', $newRole->name)->value('id_rol');
                if ($legacyRolId) {
                    $permissions = DB::table('role_permissions')->where('role_id', $newRole->id)->get();
                    foreach ($permissions as $p) {
                        DB::table('rol_permissions')->insertOrIgnore([
                            'id_rol' => $legacyRolId,
                            'permission_id' => $p->permission_id
                        ]);
                    }
                }
            }
        }

        // 3. Cleanup: Drop strictly new-RBAC tables
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-creating the roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->string('user_id', 20);
            $table->unsignedBigInteger('role_id');
            $table->primary(['user_id', 'role_id']);
        });

        Schema::dropIfExists('rol_permissions');
    }
};
