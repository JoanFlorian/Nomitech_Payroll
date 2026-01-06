<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoTrabajadorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_trabajador')->delete();

        DB::table('tipo_trabajador')->insert([
            ['id_tipo_trabajador' => '1', 'nombre' => 'Dependiente'],
            ['id_tipo_trabajador' => '2', 'nombre' => 'Servicio doméstico'],
            ['id_tipo_trabajador' => '3', 'nombre' => 'Independiente'],
            ['id_tipo_trabajador' => '4', 'nombre' => 'Madre comunitaria'],
            ['id_tipo_trabajador' => '12', 'nombre' => 'Aprendices del SENA en etapa lectiva'],
            ['id_tipo_trabajador' => '18', 'nombre' => 'Funcionarios publicos sin top maximo de ibc'],
            ['id_tipo_trabajador' => '19', 'nombre' => 'Aprendices del SENA en etapa productiva'],
            ['id_tipo_trabajador' => '21', 'nombre' => 'Estudiantes de posgrado en salud'],
            ['id_tipo_trabajador' => '22', 'nombre' => 'Profesor de establecimiento particular'],
            ['id_tipo_trabajador' => '23', 'nombre' => 'Estudiantes aportes solo riesgos laborales'],
            ['id_tipo_trabajador' => '30', 'nombre' => 'Dependiente entidades o universidades publicas con regimen especial en salud'],
            ['id_tipo_trabajador' => '31', 'nombre' => 'Cooperados o pre cooperativas de trabajo asociado'],
            ['id_tipo_trabajador' => '47', 'nombre' => 'Trabajador dependiente de entidad beneficiaria del sistema general de participaciones-aportes partronales'],
            ['id_tipo_trabajador' => '51', 'nombre' => 'Trabajador de tiempo parcial'],
            ['id_tipo_trabajador' => '54', 'nombre' => 'Pre pensionado de entidad en liquidacion'],
            ['id_tipo_trabajador' => '56', 'nombre' => 'Pre pensionado con aporte voluntario a salud'],
            ['id_tipo_trabajador' => '58', 'nombre' => 'Estudiantes de practicas laborales en el sector publico'],
        ]);
    }
}
