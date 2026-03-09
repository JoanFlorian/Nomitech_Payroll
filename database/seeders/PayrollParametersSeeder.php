<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

class PayrollParametersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payroll_parameters')->insert([
            'smmlv' => 1750905,
            'auxilio_transporte' => 249095,
            'auxilio_transporte_tope' => 2,

            'eps_employee' => 0.04,
            'pension_employee' => 0.04,
            'fondo_solidaridad' => 0.01,

            'eps_employer' => 0.085,
            'pension_employer' => 0.12,
            'arl_riesgo_1' => 0.00522,
            'caja_compensacion' => 0.04,
            

            'fondo_solidaridad_threshold' => 4,
            'horas_mes' => 240,

            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}