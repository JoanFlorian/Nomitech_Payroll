<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Empleados
            ['name' => 'view_employees', 'module' => 'Empleados', 'description' => 'Ver lista de empleados'],
            ['name' => 'create_employee', 'module' => 'Empleados', 'description' => 'Crear nuevos empleados'],
            ['name' => 'edit_employee', 'module' => 'Empleados', 'description' => 'Editar información de empleados'],
            ['name' => 'renew_contract', 'module' => 'Empleados', 'description' => 'Renovar contratos vencidos o por vencer'],
            ['name' => 'export_employees', 'module' => 'Empleados', 'description' => 'Exportar lista de empleados'],

            // Nómina
            ['name' => 'view_payroll', 'module' => 'Nómina', 'description' => 'Ver resúmenes y cálculos de nómina'],
            ['name' => 'calculate_payroll', 'module' => 'Nómina', 'description' => 'Calcular y liquidar nómina (individual o masiva)'],
            ['name' => 'export_payroll', 'module' => 'Nómina', 'description' => 'Exportar cálculos de nómina a PDF o Excel'],

            // Periodos
            ['name' => 'view_periods', 'module' => 'Periodos', 'description' => 'Ver lista de periodos de nómina'],
            ['name' => 'create_period', 'module' => 'Periodos', 'description' => 'Crear nuevos periodos de nómina'],
            ['name' => 'close_period', 'module' => 'Periodos', 'description' => 'Cerrar el periodo de nómina activo'],
            ['name' => 'export_period', 'module' => 'Periodos', 'description' => 'Exportar acumulados de un periodo a Excel'],

            // Novedades
            ['name' => 'view_novedades', 'module' => 'Novedades', 'description' => 'Ver novedades'],
            ['name' => 'create_novedad', 'module' => 'Novedades', 'description' => 'Crear nuevas novedades'],
            ['name' => 'edit_novedad', 'module' => 'Novedades', 'description' => 'Editar novedades'],
            ['name' => 'delete_novedad', 'module' => 'Novedades', 'description' => 'Eliminar novedades'],
            ['name' => 'approve_novedad', 'module' => 'Novedades', 'description' => 'Aprobar novedades'],

            // Provisiones
            ['name' => 'view_provisions', 'module' => 'Provisiones', 'description' => 'Ver listado de saldos consolidados de provisiones'],
            ['name' => 'view_provision_history', 'module' => 'Provisiones', 'description' => 'Ver historial detallado de provisiones por empleado'],
            ['name' => 'manage_provisions', 'module' => 'Provisiones', 'description' => 'Gestionar pagos de cesantías, prima y exportaciones'],

            // Reportes
            ['name' => 'view_reports', 'module' => 'Reportes', 'description' => 'Ver reportes'],
            ['name' => 'export_reports', 'module' => 'Reportes', 'description' => 'Exportar estadísticas y reportes'],

            // Nómina Electrónica
            ['name' => 'view_electronic_payroll', 'module' => 'Nómina Electrónica', 'description' => 'Ver historial de nóminas electrónicas'],
            ['name' => 'transmit_electronic_payroll', 'module' => 'Nómina Electrónica', 'description' => 'Generar y transmitir nómina electrónica a la DIAN'],

            // PILA (Seguridad Social)
            ['name' => 'view_pila', 'module' => 'PILA', 'description' => 'Ver planillas de seguridad social (PILA)'],
            ['name' => 'export_pila', 'module' => 'PILA', 'description' => 'Descargar archivos de PILA (TXT/Excel)'],

            // Catálogos
            ['name' => 'manage_catalogos', 'module' => 'Catálogos', 'description' => 'Administrar catálogos de empresa (Bancos, EPS, etc)'],
        ];


        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
