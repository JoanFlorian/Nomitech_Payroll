<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Departamento;
use App\Models\Pais;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colombia = Pais::where('codigo_alfa2', 'CO')->first();

        if (!$colombia) {
            $this->command->error('Pais Colombia not found. Please run PaisSeeder first.');
            return;
        }

        $departamentos = [
            ['codigo' => '91', 'nombre' => 'Amazonas', 'codigo_iso' => 'AMA'],
            ['codigo' => '05', 'nombre' => 'Antioquia', 'codigo_iso' => 'ANT'],
            ['codigo' => '81', 'nombre' => 'Arauca', 'codigo_iso' => 'ARA'],
            ['codigo' => '08', 'nombre' => 'Atlántico', 'codigo_iso' => 'ATL'],
            ['codigo' => '11', 'nombre' => 'Bogotá', 'codigo_iso' => 'DC'],
            ['codigo' => '13', 'nombre' => 'Bolívar', 'codigo_iso' => 'BOL'],
            ['codigo' => '15', 'nombre' => 'Boyacá', 'codigo_iso' => 'BOY'],
            ['codigo' => '17', 'nombre' => 'Caldas', 'codigo_iso' => 'CAL'],
            ['codigo' => '18', 'nombre' => 'Caquetá', 'codigo_iso' => 'CAQ'],
            ['codigo' => '85', 'nombre' => 'Casanare', 'codigo_iso' => 'CAS'],
            ['codigo' => '19', 'nombre' => 'Cauca', 'codigo_iso' => 'CAU'],
            ['codigo' => '20', 'nombre' => 'Cesar', 'codigo_iso' => 'CES'],
            ['codigo' => '27', 'nombre' => 'Chocó', 'codigo_iso' => 'CHO'],
            ['codigo' => '23', 'nombre' => 'Córdoba', 'codigo_iso' => 'COR'],
            ['codigo' => '25', 'nombre' => 'Cundinamarca', 'codigo_iso' => 'CUN'],
            ['codigo' => '94', 'nombre' => 'Guainía', 'codigo_iso' => 'GUA'],
            ['codigo' => '95', 'nombre' => 'Guaviare', 'codigo_iso' => 'GUV'],
            ['codigo' => '41', 'nombre' => 'Huila', 'codigo_iso' => 'HUI'],
            ['codigo' => '44', 'nombre' => 'La Guajira', 'codigo_iso' => 'LAG'],
            ['codigo' => '47', 'nombre' => 'Magdalena', 'codigo_iso' => 'MAG'],
            ['codigo' => '50', 'nombre' => 'Meta', 'codigo_iso' => 'MET'],
            ['codigo' => '52', 'nombre' => 'Nariño', 'codigo_iso' => 'NAR'],
            ['codigo' => '54', 'nombre' => 'Norte de Santander', 'codigo_iso' => 'NSA'],
            ['codigo' => '86', 'nombre' => 'Putumayo', 'codigo_iso' => 'PUT'],
            ['codigo' => '63', 'nombre' => 'Quindío', 'codigo_iso' => 'QUI'],
            ['codigo' => '66', 'nombre' => 'Risaralda', 'codigo_iso' => 'RIS'],
            ['codigo' => '88', 'nombre' => 'San Andrés y Providencia', 'codigo_iso' => 'SAP'],
            ['codigo' => '68', 'nombre' => 'Santander', 'codigo_iso' => 'SAN'],
            ['codigo' => '70', 'nombre' => 'Sucre', 'codigo_iso' => 'SUC'],
            ['codigo' => '73', 'nombre' => 'Tolima', 'codigo_iso' => 'TOL'],
            ['codigo' => '76', 'nombre' => 'Valle del Cauca', 'codigo_iso' => 'VAC'],
            ['codigo' => '97', 'nombre' => 'Vaupés', 'codigo_iso' => 'VAU'],
            ['codigo' => '99', 'nombre' => 'Vichada', 'codigo_iso' => 'VID'],
        ];

        foreach ($departamentos as $dep) {
            Departamento::create([
                'id_pais' => $colombia->id_pais,
                'codigo' => $dep['codigo'],
                'nombre' => $dep['nombre'],
                'codigo_iso' => $dep['codigo_iso'],
            ]);
        }
    }
}
