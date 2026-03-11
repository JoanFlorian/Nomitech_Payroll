<?php

namespace App\Services;

use App\Models\HistorialNovedad;
use App\Models\Novedad;
use Illuminate\Support\Facades\Auth;

class NovedadHistorialService
{
    /**
     * Registrar creación de novedad en el historial
     */
    public function registrarCreacion(Novedad $novedad): void
    {
        HistorialNovedad::create([
            'id_novedad' => $novedad->id_novedad,
            'id_salario' => $novedad->id_salario,
            'empleado_id' => $novedad->empleado_id,
            'tipo_novedad' => $novedad->tipo_novedad_nombre,
            'fecha_inicio' => $novedad->fecha_inicio,
            'fecha_fin' => $novedad->fecha_fin,
            'valor' => $novedad->valor_novedad,
            'observaciones' => $novedad->observaciones,
            'accion' => 'crear',
            'id_usuario' => Auth::id() ?? null,
            'usuario_nombre' => Auth::user()?->name ?? 'Sistema',
        ]);
    }

    /**
     * Registrar actualización de novedad en el historial
     */
    public function registrarActualizacion(Novedad $novedadAnterior, Novedad $novedadNueva): void
    {
        // Solo registrar si hubo cambios
        if ($this->hayaCambios($novedadAnterior, $novedadNueva)) {
            HistorialNovedad::create([
                'id_novedad' => $novedadNueva->id_novedad,
                'id_salario' => $novedadNueva->id_salario,
                'empleado_id' => $novedadNueva->empleado_id,
                'tipo_novedad' => $novedadNueva->tipo_novedad_nombre,
                'fecha_inicio' => $novedadNueva->fecha_inicio,
                'fecha_fin' => $novedadNueva->fecha_fin,
                'valor' => $novedadNueva->valor_novedad,
                'observaciones' => $novedadNueva->observaciones,
                'accion' => 'actualizar',
                'id_usuario' => Auth::id() ?? null,
                'usuario_nombre' => Auth::user()?->name ?? 'Sistema',
            ]);
        }
    }

    /**
     * Registrar eliminación de novedad en el historial
     */
    public function registrarEliminacion(Novedad $novedad): void
    {
        HistorialNovedad::create([
            'id_novedad' => $novedad->id_novedad,
            'id_salario' => $novedad->id_salario,
            'empleado_id' => $novedad->empleado_id,
            'tipo_novedad' => $novedad->tipo_novedad_nombre,
            'fecha_inicio' => $novedad->fecha_inicio,
            'fecha_fin' => $novedad->fecha_fin,
            'valor' => $novedad->valor_novedad,
            'observaciones' => $novedad->observaciones,
            'accion' => 'eliminar',
            'id_usuario' => Auth::id() ?? null,
            'usuario_nombre' => Auth::user()?->name ?? 'Sistema',
        ]);
    }

    /**
     * Verificar si hubo cambios entre dos novedades
     */
    private function hayaCambios(Novedad $anterior, Novedad $nueva): bool
    {
        return $anterior->tipo_novedad_nombre !== $nueva->tipo_novedad_nombre
            || $anterior->fecha_inicio !== $nueva->fecha_inicio
            || $anterior->fecha_fin !== $nueva->fecha_fin
            || $anterior->valor_novedad !== $nueva->valor_novedad
            || $anterior->observaciones !== $nueva->observaciones
            || $anterior->dias !== $nueva->dias
            || $anterior->horas !== $nueva->horas;
    }

    /**
     * Obtener historial de una novedad específica
     */
    public function obtenerHistorialNovedad(int $idNovedad)
    {
        return HistorialNovedad::where('id_novedad', $idNovedad)
            ->orderByDesc('created_at')
            ->get();
    }
}
