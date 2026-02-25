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
        // User::factory(10)->create();


        $this->call([
            PaisSeeder::class,
            DepartamentoSeeder::class,
            CiudadSeeder::class,
            RolSeeder::class,
            BancoSeeder::class,
<<<<<<< HEAD
            TipoDocSeeder::class,
=======
            EPSSeeder::class,
            AFPSeeder::class,
            ARLSeeder::class,
            TipoDocSeeder::class, 
>>>>>>> af105929fff92a0e463cda1a326762fda9c6a73f
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
            FormaPagoSeeder::class,
            MetodoPagoSeeder::class,
            SubTipoTrabajadorSeeder::class,
<<<<<<< HEAD
=======
            AFPSeeder::class,
            ARLSeeder::class,
            EPSSeeder::class,
            FormaPagoSeeder::class,
            TipoCuentaSeeder::class,

>>>>>>> af105929fff92a0e463cda1a326762fda9c6a73f

        ]);
    }
}
