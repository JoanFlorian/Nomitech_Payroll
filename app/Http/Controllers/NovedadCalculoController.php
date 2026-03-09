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

        $salarioBase = $this->calculoNovedadService->resolverSalarioBase($salario);
        $resultado = $this->calculoNovedadService->calcularNovedad(array_merge($data, ['salario_base' => $salarioBase]));
        $tipoNovedad = (string) ($resultado['tipo_novedad'] ?? $data['tipo_novedad'] ?? '');
        $operacion = (string) ($resultado['tipo_movimiento'] ?? CalculoNovedadService::OPERACION_SIN_MOVIMIENTO);
        $esAutomatica = $this->calculoNovedadService->esNovedadAutomatica($tipoNovedad);

        return response()->json([
            'success' => true,
            'valor' => (float) ($resultado['valor_calculado'] ?? 0),
            'operacion' => $operacion,
            'tipo_movimiento' => $operacion,
            'afecta_ibc' => (bool) ($resultado['afecta_ibc'] ?? false),
            'valor_dia' => (float) ($resultado['valor_dia'] ?? 0),
            'valor_hora' => (float) ($resultado['valor_hora'] ?? 0),
            'salario_base' => $salarioBase,
            'es_automatica' => $esAutomatica,
            'usa_pago_manual' => isset($data['valor_manual']) && $data['valor_manual'] !== null && $data['valor_manual'] !== '',
        ]);
    }
}
