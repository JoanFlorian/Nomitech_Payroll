<?php

namespace Database\Seeders;

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
            Otros::class,
=======
            TipoDocSeeder::class, 
            TipoTrabajadorSeeder::class,
            SubTipoTrabajadorSeeder::class,
            TipoContratoSeeder::class,
            TipoHoraRecargoSeeder::class,
>>>>>>> 5c1c2bf2678d7c89186b6537f714773bead188a2
        ]);
    }
}
