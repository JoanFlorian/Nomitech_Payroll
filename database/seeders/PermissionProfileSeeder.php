<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Empresa;

class PermissionProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $auxiliarPermissions = [
            'view_employees',
            'view_payroll',
            'view_periods',
            'view_novedades',
            'create_novedad',
            'edit_novedad',
            'view_provisions',
            'view_electronic_payroll',
            'view_pila',
        ];

        $auditorPermissions = [
            'view_employees', 'export_employees',
            'view_payroll', 'export_payroll',
            'view_periods', 'export_period',
            'view_novedades',
            'view_provisions', 'view_provision_history',
            'view_reports', 'export_reports',
            'view_electronic_payroll',
            'view_pila', 'export_pila',
        ];

        $auxiliarIds = Permission::whereIn('name', $auxiliarPermissions)->pluck('id')->toArray();
        $auditorIds = Permission::whereIn('name', $auditorPermissions)->pluck('id')->toArray();

        // Aplicar a todas las empresas existentes
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            // Auxiliar
            $auxiliarRole = Role::where('name', 'Auxiliar de Nómina')
                                ->where('company_id', $empresa->id_empresa)
                                ->first();
            
            if ($auxiliarRole) {
                $auxiliarRole->permissions()->sync($auxiliarIds);
            }

            // Auditor
            $auditorRole = Role::where('name', 'Auditor de Nómina')
                               ->where('company_id', $empresa->id_empresa)
                               ->first();

            if ($auditorRole) {
                $auditorRole->permissions()->sync($auditorIds);
            }
        }
    }
}
