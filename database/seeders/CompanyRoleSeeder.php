<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permission;

class CompanyRoleSeeder extends Seeder
{
    /**
     * Seed roles for a specific company or all companies.
     * 
     * @param int|null $companyId
     */
    public function run($companyId = null): void
    {
        // Seeding global permissions for legacy roles
        // companyId is ignored as roles are now global
        $this->seedGlobalPermissions();
    }

    private function seedGlobalPermissions()
    {
        $roles = [
            [
                'name' => 'Administrador',
                'permissions' => '*'
            ],
            [
                'name' => 'Representante Legal',
                'permissions' => '*'
            ],
            [
                'name' => 'Empleado',
                'permissions' => ['view_payroll']
            ],
            [
                'name' => 'Auxiliar de Nómina',
                'permissions' => [
                    'view_employees', 'view_payroll', 'view_periods', 
                    'view_novedades', 'create_novedad', 'edit_novedad',
                    'view_provisions', 'view_electronic_payroll', 'view_pila'
                ]
            ],
            [
                'name' => 'Auditor de Nómina',
                'permissions' => [
                    'view_employees', 'export_employees', 'view_payroll', 'export_payroll',
                    'view_periods', 'export_period', 'view_novedades',
                    'view_provisions', 'view_provision_history', 'view_reports', 'export_reports',
                    'view_electronic_payroll', 'view_pila', 'export_pila'
                ]
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Rol::where('nombre', $roleData['name'])->first();
            if (!$role) continue;

            if ($roleData['permissions'] === '*') {
                $role->permissions()->sync(Permission::all()->pluck('id'));
            } else {
                $permissionIds = Permission::whereIn('name', $roleData['permissions'])->pluck('id');
                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
