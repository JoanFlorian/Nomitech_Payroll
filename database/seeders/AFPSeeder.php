<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Afp;

class AFPSeeder extends Seeder
{
    public function run(): void
    {
        $afpList = [
            ['id_afp' => 1, 'nombre' => 'Protección'],
            ['id_afp' => 2, 'nombre' => 'Colfondos'],
            ['id_afp' => 3, 'nombre' => 'Porvenir'],
            ['id_afp' => 4, 'nombre' => 'Old Mutua'],
        ];

        foreach ($afpList as $afp) {
            Afp::updateOrCreate(
                ['id_afp' => $afp['id_afp']],
                $afp
            );
        }

        $this->command->info('✅ AFP cargadas: ' . count($afpList) . ' registros');
    }
}
