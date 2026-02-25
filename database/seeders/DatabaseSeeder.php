<?php

namespace Database\Seeders;

use App\Models\Afp;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PaisSeeder::class,
            DepartamentoSeeder::class,
            CiudadSeeder::class,
            RolSeeder::class,
            BancoSeeder::class,
            EPSSeeder::class,
            AFPSeeder::class,
            ARLSeeder::class,
            TipoDocSeeder::class,
            TipoTrabajadorSeeder::class,
            TipoCuentaSeeder::class,
            FormaPagoSeeder::class,
            MetodoPagoSeeder::class,
            LicenciaSeeder::class,
            PagoSeeder::class,
            PlanSeeder::class,
            TipoContratoSeeder::class,
            SuperAdminSeeder::class,
            TipoHoraRecargoSeeder::class,
            SubTipoTrabajadorSeeder::class,
        ]);
    }
}
