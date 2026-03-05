<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewNovedadCalculoRequest;
use App\Services\CalculoNovedadService;

class NovedadCalculoController extends Controller
{
    public function __construct(private readonly CalculoNovedadService $calculoNovedadService)
    {
    }

    public function preview(PreviewNovedadCalculoRequest $request)
    {
        $data = $request->validated();
        $salario = $this->calculoNovedadService->obtenerSalarioEmpleado((string) $data['empleado_id']);

        if (!$salario) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro salario base para el empleado seleccionado.',
            ], 422);
        }

        $salarioBase = (float) ($salario->contrato_salario_base ?? optional($salario->contrato)->salario_base ?? 0);
        $valor = $this->calculoNovedadService->calcularValor($data, $salarioBase);
        $operacion = $this->calculoNovedadService->resolverOperacion((string) ($data['tipo_novedad'] ?? ''));

        return response()->json([
            'success' => true,
            'valor' => $valor,
            'operacion' => $operacion,
            'salario_base' => $salarioBase,
            'usa_pago_manual' => isset($data['pago_manual']) && $data['pago_manual'] !== null && $data['pago_manual'] !== '',
        ]);
    }
}
