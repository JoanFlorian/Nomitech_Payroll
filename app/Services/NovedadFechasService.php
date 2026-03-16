<?php

namespace App\Services;

use Carbon\Carbon;

class NovedadFechasService
{
    /**
     * Duraciones fijas para tipos de novedades (en días)
     */
    private const DURACIONES_FIJAS = [
        'LMAT' => 126,  // Licencia de maternidad: 126 días (18 semanas)
        'LPAT' => 14,   // Licencia de paternidad: 14 días (Ley 2114 de 2021)
        'VAC'  => 15,   // Vacaciones: 15 días hábiles
        'LIC'  => 30,   // Licencia general: 30 días
    ];

    /**
     * Calcular fecha fin basada en tipo de novedad y fecha inicio
     */
    public function calcularFechaFin(string $tipoNovedad, ?string $fechaInicio = null, ?int $dias = null, ?int $horas = null): ?string
    {
        if (!$fechaInicio) {
            return null;
        }

        $codigo = $this->extraerCodigoTipo($tipoNovedad);
        
        // Si ya hay una duración especificada en días, usarla (fecha_fin es el último día, inclusive)
        if ($dias && $dias > 0) {
            return Carbon::parse($fechaInicio)->addDays((int) $dias - 1)->format('Y-m-d');
        }

        // Si es una novedad con duración fija, aplicarla (fecha_fin inclusive: día 1 = fecha_inicio)
        if (isset(self::DURACIONES_FIJAS[$codigo])) {
            $diasFijos = self::DURACIONES_FIJAS[$codigo];
            return Carbon::parse($fechaInicio)->addDays($diasFijos - 1)->format('Y-m-d');
        }

        // Para novedades sin duración fija (ej. VSP, TDE, etc.), retornar la misma fecha
        return $fechaInicio;
    }

    /**
     * Obtener la duración fija de un tipo de novedad
     */
    public function obtenerDuracionFija(string $tipoNovedad): ?int
    {
        $codigo = $this->extraerCodigoTipo($tipoNovedad);
        return self::DURACIONES_FIJAS[$codigo] ?? null;
    }

    /**
     * Extraer el código de 4 caracteres del tipo de novedad
     */
    private function extraerCodigoTipo(string $tipoNovedad): string
    {
        // Buscar el código entre los primeros caracteres
        if (preg_match('/^([A-Z]+)\s*-/', $tipoNovedad, $matches)) {
            return $matches[1];
        }

        // Si no hay guión, asumir que es el código completo
        return strtoupper(trim($tipoNovedad));
    }

    /**
     * Verificar si una novedad tiene duración fija a calcular automáticamente
     */
    public function tieneDuracionFija(string $tipoNovedad): bool
    {
        return !is_null($this->obtenerDuracionFija($tipoNovedad));
    }
}
