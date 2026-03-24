<?php

namespace Database\Factories;

use App\Models\Contrato;
use App\Models\Usuario;
use App\Models\Empresa;
use App\Models\TipoContrato;
use App\Models\TipoTrabajador;
use App\Models\SubTipoTrabajador;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\Arl;
use App\Models\Eps;
use App\Models\Afp;
use App\Models\CajaCompensacion;
use App\Models\NivelRiesgo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContratoFactory extends Factory
{
    protected $model = Contrato::class;

    public function definition(): array
    {
        return [
            'doc' => Usuario::factory(),
            'id_empresa' => Empresa::factory(),
            'id_tipo_contrato' => TipoContrato::first()?->id_tipo_contrato ?? 1,
            'id_tipo_trabajador' => TipoTrabajador::first()?->id_tipo_trabajador ?? 1,
            'id_sub_tipo_trabajador' => SubTipoTrabajador::first()?->id_sub_tipo_trabajador ?? 1,
            'id_forma_pago' => FormaPago::first()?->id_forma_pago ?? 1,
            'id_metodo_pago' => MetodoPago::first()?->id_metodo_pago ?? 1,
            'id_arl' => Arl::first()?->id_arl ?? 1,
            'id_eps' => Eps::first()?->id_eps ?? 1,
            'id_afp' => Afp::first()?->id_afp ?? 1,
            'id_caja' => CajaCompensacion::first()?->id_caja ?? 1,
            'fecha_inicio' => now()->subMonths(6),
            'fecha_fin' => now()->addMonths(6),
            'salario_base' => 1300000,
            'salario' => 1300000,
            'activo' => true,
            'alto_riesgo' => false,
            'nivel_riesgo_id' => NivelRiesgo::first()?->id ?? 1,
            'horas_diarias' => 8,
            'codigo_interno' => fake()->numerify('EMP-####'),
            'tipo_cuenta' => 'AHORROS',
            'numero_cuenta' => fake()->bankAccountNumber(),
            'estado' => Contrato::ESTADO_ACTIVO,
        ];
    }
}
