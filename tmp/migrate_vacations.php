<?php

use App\Models\BenefitBalance;
use App\Models\Contrato;
use Illuminate\Support\Facades\DB;

/**
 * Script de migración: Convierte saldos de vacaciones de Pesos ($) a Días.
 * Fórmula: Días = Saldo_en_pesos / (Salario_Base / 30)
 */

return DB::transaction(function () {
    $balances = BenefitBalance::where('vacaciones_balance', '>', 0)->get();
    $migratedCount = 0;
    $errors = [];

    foreach ($balances as $balance) {
        // Obtener el contrato activo del empleado
        $contrato = Contrato::where('doc', $balance->employee_id)
            ->where('id_empresa', $balance->tenant_id)
            ->where('estado', Contrato::ESTADO_ACTIVO)
            ->first();

        if (!$contrato || $contrato->salario_base <= 0) {
            $errors[] = "Empleado {$balance->employee_id}: No se encontró contrato activo o salario válido.";
            continue;
        }

        $salarioBalance = (float) $balance->vacaciones_balance;
        $valorDia = (float) $contrato->salario_base / 30;
        
        $nuevosDias = round($salarioBalance / $valorDia, 2);

        // Actualizar el balance
        $balance->update(['vacaciones_balance' => $nuevosDias]);
        
        // Opcional: Registrar un ledger entry de ajuste para trazabilidad
        \App\Models\BenefitLedger::create([
            'tenant_id' => $balance->tenant_id,
            'employee_id' => $balance->employee_id,
            'contract_id' => $contrato->id_contrato,
            'benefit_type' => 'vacaciones',
            'movement_type' => 'adjustment',
            'amount' => 0, // El balance ya se actualizó arriba
            'source' => 'migration',
            'reference' => "Migración técnica: \${$salarioBalance} convertidos a {$nuevosDias} días.",
        ]);

        $migratedCount++;
    }

    return [
        'success' => true,
        'migrated' => $migratedCount,
        'errors' => $errors
    ];
});
