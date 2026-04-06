<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesExportResponses;
use App\Models\Usuario;
use App\Models\Contrato;
use App\Models\Salario;
use App\Models\HistorialNovedad;
use App\Http\Requests\UpdatePerfilRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TrabajadorController extends Controller
{
    use HandlesExportResponses;

    /**
     * Dashboard principal del trabajador
     */
    public function index()
    {
        $usuario = Auth::user();
        $fechaInicioTrabajador = Contrato::where('doc', $usuario->doc)
            ->whereNotNull('fecha_inicio')
            ->orderBy('fecha_inicio', 'asc')
            ->value('fecha_inicio');
        
        // Obtener contratos del trabajador
        $contratos = Contrato::where('doc', $usuario->doc)->get();
        $contratoIds = $contratos->pluck('id_contrato');
        
        // Obtener último desprendible pagado
        $ultimoSalario = Salario::whereIn('id_contrato', $contratoIds)
            ->where('estado', Salario::ESTADO_PAGADO)
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Estadísticas del último pago
        $ultimoPago = [
            'fecha' => $ultimoSalario ? $ultimoSalario->created_at->format('d/m/Y') : 'N/A',
            'total_devengado' => $ultimoSalario ? $ultimoSalario->total_devengos : 0,
            'total_deducciones' => $ultimoSalario ? $ultimoSalario->total_deducciones : 0,
            'neto_pagado' => $ultimoSalario ? $ultimoSalario->salario_neto : 0,
        ];
        
        return view('trabajador.dashboard', compact('usuario', 'ultimoPago', 'fechaInicioTrabajador'));
    }
    
    /**
     * Listado de desprendibles del trabajador
     */
    public function desprendibles()
    {
        $usuario = Auth::user();
        
        // Obtener contratos del trabajador
        $contratos = Contrato::where('doc', $usuario->doc)->get();
        $contratoIds = $contratos->pluck('id_contrato');
        
        // Obtener desprendibles con información del periodo
        $desprendibles = Salario::with(['periodo', 'contrato'])
            ->whereIn('id_contrato', $contratoIds)
            ->whereIn('estado', [Salario::ESTADO_LIQUIDADO, Salario::ESTADO_PAGADO])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('trabajador.desprendibles', compact('desprendibles'));
    }
    
    /**
     * Ver detalle de un desprendible específico
     */
    public function verDesprendible($id)
    {
        $usuario = Auth::user();
        
        // Obtener contratos del trabajador
        $contratos = Contrato::where('doc', $usuario->doc)->get();
        $contratoIds = $contratos->pluck('id_contrato');
        
        // Buscar el desprendible con relaciones extendidas
        $desprendible = Salario::with([
            'contrato.usuario',
            'contrato.tipoContrato',
            'contrato.metodoPago',
            'contrato.formaPago',
            'periodo',
            'novedades.tipoNovedad',
        ])
            ->whereIn('id_contrato', $contratoIds)
            ->where('id_salario', $id)
            ->first();
        
        // Verificar que el desprendible pertenece al trabajador
        if (!$desprendible) {
            abort(403, 'No tienes permiso para ver este desprendible');
        }

        // Cargar desglose de horas extras y recargos
        $horasExtras = \App\Models\HoraRecargoExtra::with('tipoHoraRecargo')
            ->where('id_salario', $desprendible->id_salario)
            ->get()
            ->filter(fn($h) => $h->tipoHoraRecargo !== null && (float) $h->cantidad > 0)
            ->values();
        
        // Organizar devengos y deducciones
        $devengos = [
            'Salario Base' => $desprendible->salario_base ?? $desprendible->contrato->salario_base ?? 0,
            'Auxilio de Transporte' => $desprendible->auxilio_transporte ?? 0,
            'Horas Extras y Recargos' => $desprendible->valor_horas_extras_recargos ?? $desprendible->horas_extra ?? 0,
            'Bonificaciones' => $desprendible->bonificaciones ?? 0,
            'Comisiones' => $desprendible->comisiones ?? 0,
            'Otros Devengos' => $desprendible->otros_devengos ?? 0,
        ];
        
        $deducciones = [
            'EPS' => $desprendible->eps ?? 0,
            'AFP (Pensión)' => $desprendible->afp ?? 0,
            'Fondo de Solidaridad' => $desprendible->aporte_fp ?? 0,
            'Retención en la Fuente' => $desprendible->retencion_fuente ?? 0,
            'Embargo Fiscal' => $desprendible->embargo_fiscal ?? 0,
            'Pensión Voluntaria' => $desprendible->pension_voluntaria ?? 0,
        ];
        
        // Agregar novedades a devengos/deducciones
        foreach ($desprendible->novedades as $novedad) {
            if ($novedad->pago > 0) {
                $devengos[$novedad->tipo_novedad_nombre ?? $novedad->concepto ?? 'Novedad'] = $novedad->pago;
            } elseif ($novedad->pago < 0) {
                $deducciones[$novedad->tipo_novedad_nombre ?? $novedad->concepto ?? 'Novedad'] = abs($novedad->pago);
            }
        }
        
        return view('trabajador.ver-desprendible', compact('desprendible', 'devengos', 'deducciones', 'horasExtras'));
    }
    
    /**
     * Descargar desprendible en formato PDF
     */
    public function descargarDesprendible($id)
    {
        $usuario = Auth::user();
        
        // Obtener contratos del trabajador
        $contratos = Contrato::where('doc', $usuario->doc)->get();
        $contratoIds = $contratos->pluck('id_contrato');
        
        // Buscar el desprendible con relaciones extendidas
        $desprendible = Salario::with([
            'contrato.usuario',
            'contrato.tipoContrato',
            'contrato.metodoPago',
            'contrato.formaPago',
            'periodo',
            'novedades.tipoNovedad',
        ])
            ->whereIn('id_contrato', $contratoIds)
            ->where('id_salario', $id)
            ->first();
        
        // Verificar que el desprendible pertenece al trabajador
        if (!$desprendible) {
            abort(403, 'No tienes permiso para descargar este desprendible');
        }

        // Cargar desglose de horas extras y recargos
        $horasExtras = \App\Models\HoraRecargoExtra::with('tipoHoraRecargo')
            ->where('id_salario', $desprendible->id_salario)
            ->get()
            ->filter(fn($h) => $h->tipoHoraRecargo !== null && (float) $h->cantidad > 0)
            ->values();
        
        // Organizar devengos y deducciones
        $devengos = [
            'Salario Base' => $desprendible->salario_base ?? $desprendible->contrato->salario_base ?? 0,
            'Auxilio de Transporte' => $desprendible->auxilio_transporte ?? 0,
            'Horas Extras y Recargos' => $desprendible->valor_horas_extras_recargos ?? $desprendible->horas_extra ?? 0,
            'Bonificaciones' => $desprendible->bonificaciones ?? 0,
            'Comisiones' => $desprendible->comisiones ?? 0,
            'Otros Devengos' => $desprendible->otros_devengos ?? 0,
        ];
        
        $deducciones = [
            'EPS' => $desprendible->eps ?? 0,
            'AFP (Pensión)' => $desprendible->afp ?? 0,
            'Fondo de Solidaridad' => $desprendible->aporte_fp ?? 0,
            'Retención en la Fuente' => $desprendible->retencion_fuente ?? 0,
            'Embargo Fiscal' => $desprendible->embargo_fiscal ?? 0,
            'Pensión Voluntaria' => $desprendible->pension_voluntaria ?? 0,
        ];
        
        // Agregar novedades
        foreach ($desprendible->novedades as $novedad) {
            if ($novedad->pago > 0) {
                $devengos[$novedad->tipo_novedad_nombre ?? $novedad->concepto ?? 'Novedad'] = $novedad->pago;
            } elseif ($novedad->pago < 0) {
                $deducciones[$novedad->tipo_novedad_nombre ?? $novedad->concepto ?? 'Novedad'] = abs($novedad->pago);
            }
        }
        
        // Generar PDF
        $pdf = Pdf::loadView('trabajador.pdf-desprendible', compact('desprendible', 'devengos', 'deducciones', 'horasExtras'));

        $periodoNombre = $desprendible->periodo->nombre ?? 'periodo';
        $periodoNombre = preg_replace('/[^A-Za-z0-9_-]+/u', '_', $periodoNombre) ?: 'periodo';
        $filename = 'desprendible_' . trim($periodoNombre, '_') . '.pdf';

        return $this->downloadPdfResponse($pdf, $filename);
    }
    
    /**
     * Listado de notas de ajuste del trabajador
     */
    public function notasAjuste()
    {
        $usuario = Auth::user();
        
        // Obtener notas de ajuste del trabajador
        $notasEnviadas = HistorialNovedad::with(['novedad', 'salario.periodo'])
            ->where('empleado_id', $usuario->doc)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('trabajador.notas', compact('notasEnviadas'));
    }
    
    /**
     * Ver perfil del trabajador
     */
    public function perfil()
    {
        $usuario = Auth::user();
        
        return view('trabajador.perfil', compact('usuario'));
    }

    /**
     * Actualizar solo la foto de perfil del trabajador autenticado.
     */
    public function actualizarFotoPerfil(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|file|mimes:jpg,jpeg,png|max:2048',
        ], [
            'avatar.required' => 'Debes seleccionar una imagen.',
            'avatar.file' => 'El archivo seleccionado no es válido.',
            'avatar.mimes' => 'La foto debe estar en formato JPG, JPEG o PNG.',
            'avatar.max' => 'La foto no puede superar 2MB.',
        ]);

        $usuario = Usuario::where('doc', Auth::user()->doc)->firstOrFail();

        if (!empty($usuario->avatar) && Storage::disk('public')->exists($usuario->avatar)) {
            Storage::disk('public')->delete($usuario->avatar);
        }

        $path = $validated['avatar']->store('empleados', 'public');
        $usuario->update(['avatar' => $path]);

        return redirect()->route('trabajador.perfil')
            ->with('success', 'Foto de perfil actualizada correctamente.');
    }
    
    /**
     * Actualizar perfil del trabajador
     */
    public function actualizarPerfil(UpdatePerfilRequest $request)
    {
        $usuario = Usuario::where('doc', Auth::user()->doc)->firstOrFail();
        
        // Solo actualizar campos permitidos (documento no se modifica)
        $ucWords = fn(?string $v) => $v ? mb_convert_case(mb_strtolower($v, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : $v;

        $usuario->update([
            'primer_nombre'   => $ucWords($request->primer_nombre),
            'otros_nombres'   => $ucWords($request->otros_nombres),
            'primer_apellido' => $ucWords($request->primer_apellido),
            'segundo_apellido'=> $ucWords($request->segundo_apellido),
            'id_tipo_doc'     => $request->id_tipo_doc,
            'telefono'        => $request->telefono,
            'correo'          => $request->correo,
            'direccion'       => $request->direccion,
            'id_ciudad'       => $request->id_ciudad,
        ]);
        
        return redirect()->route('trabajador.perfil')
            ->with('success', 'Perfil actualizado correctamente');
    }
}
