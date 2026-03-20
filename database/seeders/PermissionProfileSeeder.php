<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
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
            // Empleados
            'view_employees',
            'create_employee',
            'edit_employee',
            'export_employees',
            
            // Nómina
            'view_payroll',
            'calculate_payroll',
            'export_payroll',
            
            // Periodos
            'view_periods',
            'create_period',
            'export_period',
            
            // Novedades
            'view_novedades',
            'create_novedad',
            'edit_novedad',
            'delete_novedad',
            'approve_novedad',
            
            // Provisiones
            'view_provisions',
            'view_provision_history',
            
            // Reportes
            'view_reports',
            'export_reports',
            
            // Nómina Electrónica
            'view_electronic_payroll',
            
            // PILA
            'view_pila',
            'export_pila',
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

        // Admin permissions (all)
        $adminIds = Permission::pluck('id')->toArray();

        // Assign to global roles
        $auxiliarRole = Rol::where('nombre', 'Auxiliar de Nómina')->first();
        if ($auxiliarRole) {
            $auxiliarRole->permissions()->sync($auxiliarIds);
        }

        $auditorRole = Rol::where('nombre', 'Auditor de Nómina')->first();
        if ($auditorRole) {
            $auditorRole->permissions()->sync($auditorIds);
        }

        $adminRole = Rol::where('nombre', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync($adminIds);
        }
        
        $legalRole = Rol::where('nombre', 'Representante Legal')->first();
        if ($legalRole) {
            $legalRole->permissions()->sync($adminIds);
        }
    }
}
