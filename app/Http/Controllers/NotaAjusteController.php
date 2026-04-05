<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\HoraRecargoExtra;
use App\Models\NotaAjuste;
use App\Models\NotaAjusteDetalle;
use App\Models\Salario;
use App\Models\TipoHoraRecargo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class NotaAjusteController extends Controller
{
    private const CAMPOS_AJUSTABLES_PAGO = [
        'auxilio_transporte',
        'valor_horas_extras_recargos',
        'bonificaciones',
        'comisiones',
        'otros_devengos',
        'eps',
        'afp',
        'aporte_fp',
        'retencion_fuente',
        'embargo_fiscal',
        'pension_voluntaria',
    ];

    /** Campos que constituyen base de seguridad social (son salariales) */
    private const CAMPOS_SALARIALES = [
        'valor_horas_extras_recargos',
        'bonificaciones',
        'comisiones',
        'otros_devengos',
    ];

    /** Campos de deducción: al incrementar, reducen el neto del empleado */
    private const CAMPOS_DEDUCCION = [
        'eps',
        'afp',
        'aporte_fp',
        'retencion_fuente',
        'embargo_fiscal',
        'pension_voluntaria',
    ];

    public function trabajadorIndex(): View
    {
        $usuario = Auth::user();

        $notasEnviadas = NotaAjuste::query()
            ->with(['salario.periodo'])
            ->where('usuario_id', $usuario->doc)
            ->latest()
            ->paginate(10, ['*'], 'notas_page');

        return view('trabajador.notas', compact('notasEnviadas'));
    }

    public function createForDesprendible(int $idSalario): View
    {
        $usuario = Auth::user();

        $desprendible = Salario::with(['periodo', 'contrato'])
            ->where('id_salario', $idSalario)
            ->firstOrFail();

        abort_unless((string) ($desprendible->contrato->doc ?? '') === (string) $usuario->doc, 403);

        return view('trabajador.notas-create', compact('desprendible'));
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        $validated = $request->validate([
            'id_salario' => 'bail|required|integer|exists:salario,id_salario',
            'mensaje' => 'bail|required|string|min:10|max:2000',
        ], [
            'id_salario.required' => 'Debes seleccionar un desprendible para enviar la nota.',
            'id_salario.exists' => 'El desprendible seleccionado no es válido.',
            'mensaje.required' => 'Debes escribir una nota de ajuste.',
            'mensaje.min' => 'La nota de ajuste debe tener al menos 10 caracteres.',
            'mensaje.max' => 'La nota de ajuste no puede superar 2000 caracteres.',
        ]);

        $desprendible = Salario::with('contrato')
            ->where('id_salario', (int) $validated['id_salario'])
            ->firstOrFail();

        abort_unless((string) ($desprendible->contrato->doc ?? '') === (string) $usuario->doc, 403);

        $empresaId = $this->resolveCompanyId($usuario->doc);

        NotaAjuste::create([
            'usuario_id' => $usuario->doc,
            'id_salario' => (int) $validated['id_salario'],
            'id_empresa' => $empresaId,
            'mensaje' => trim($validated['mensaje']),
            'estado' => NotaAjuste::ESTADO_PENDIENTE,
        ]);

        return redirect()
            ->route('trabajador.notas')
            ->with('success', 'La nota de ajuste fue enviada correctamente al administrador.');
    }

    public function destroy(NotaAjuste $notaAjuste): RedirectResponse
    {
        $usuario = Auth::user();

        abort_unless((string) $notaAjuste->usuario_id === (string) $usuario->doc, 403);
        abort_unless($notaAjuste->estado === NotaAjuste::ESTADO_PENDIENTE, 403);

        $notaAjuste->delete();

        return redirect()
            ->route('trabajador.notas')
            ->with('success', 'La nota pendiente fue eliminada correctamente.');
    }

    public function adminIndex(): View
    {
        $this->authorizeAdminAccess();

        $notas = $this->adminNotesQuery()
            ->with(['salario.periodo'])
            ->whereIn('estado', [NotaAjuste::ESTADO_PENDIENTE, NotaAjuste::ESTADO_RESUELTO])
            ->orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20);

        return view('admin.notas-ajuste.index', compact('notas'));
    }

    public function show(NotaAjuste $notaAjuste): View
    {
        $this->authorizeAdminAccess();

        abort_unless($this->canAccessAdminNote($notaAjuste), 403);

        $notaAjuste->load(['usuario', 'salario.periodo', 'salario.contrato', 'detalles']);

        return view('admin.notas-ajuste.show', [
            'nota' => $notaAjuste,
        ]);
    }

    public function adminUpdate(Request $request, NotaAjuste $notaAjuste): RedirectResponse
    {
        $this->authorizeAdminAccess();

        abort_unless($this->canAccessAdminNote($notaAjuste), 403);

        $validated = $request->validate([
            'respuesta_admin' => 'bail|required|string|min:5|max:2000',
            'accion' => 'bail|required|in:resuelto,omitido',
        ], [
            'respuesta_admin.required' => 'Debes escribir una respuesta para la nota.',
            'respuesta_admin.min' => 'La respuesta debe tener al menos 5 caracteres.',
            'respuesta_admin.max' => 'La respuesta no puede superar 2000 caracteres.',
            'accion.required' => 'Debes seleccionar una acción para la nota.',
            'accion.in' => 'La acción seleccionada no es válida.',
        ]);

        $nuevoEstado = $validated['accion'] === 'omitido'
            ? NotaAjuste::ESTADO_OMITIDO
            : NotaAjuste::ESTADO_RESUELTO;

        $notaAjuste->update([
            'respuesta_admin' => trim($validated['respuesta_admin']),
            'estado' => $nuevoEstado,
        ]);

        if ($nuevoEstado === NotaAjuste::ESTADO_OMITIDO) {
            return redirect()
                ->route('admin.notas-ajuste.index')
                ->with('success', 'La nota fue omitida y ya no aparecerá en el panel de administración.');
        }

        return redirect()
            ->route('admin.notas-ajuste.show', $notaAjuste)
            ->with('success', 'La nota fue respondida y marcada como resuelta.');
    }

    public function adminApplyPaymentAdjustment(Request $request, NotaAjuste $notaAjuste): RedirectResponse
    {
        $this->authorizeAdminAccess();

        abort_unless($this->canAccessAdminNote($notaAjuste), 403);

        $notaAjuste->loadMissing(['salario.periodo', 'salario.contrato', 'detalles']);

        abort_unless($notaAjuste->salario !== null, 404, 'No se encontró el desprendible asociado a la nota.');
        abort_if($notaAjuste->estado === NotaAjuste::ESTADO_OMITIDO, 422, 'No se pueden aplicar ajustes sobre una nota omitida.');

        $salario  = $notaAjuste->salario;
        $adminDoc = (string) (Auth::user()->doc ?? Auth::id());
        $ahora    = now();

        // ── PATH A: detalles pre-guardados → usarlos directamente (más seguro) ─
        $detallesGuardados = $notaAjuste->detalles;

        if ($detallesGuardados->isNotEmpty()) {
            $before  = [];
            $updates = [];

            foreach ($detallesGuardados as $detalle) {
                $campo         = $detalle->campo;
                $valorActual   = round((float) ($salario->{$campo} ?? 0), 2);
                $valorCorregido = round((float) $detalle->valor_corregido, 2);

                if (abs($valorActual - $valorCorregido) < 0.00001) {
                    continue;
                }

                $before[$campo]  = $valorActual;
                $updates[$campo] = $valorCorregido;
            }

            if (empty($updates)) {
                return redirect()
                    ->route('admin.notas-ajuste.show', $notaAjuste)
                    ->with('warning', 'No se aplicaron cambios porque las correcciones guardadas coinciden con los valores actuales.');
            }

            DB::transaction(function () use ($salario, $updates, $notaAjuste, $adminDoc, $ahora) {
                $salario->update($updates);

                NotaAjusteDetalle::where('nota_id', $notaAjuste->id)
                    ->update(['aprobado_por' => $adminDoc, 'aprobado_en' => $ahora]);
            });

            Log::info('Ajuste aplicado desde nota_ajuste_detalles pre-guardados', [
                'nota_ajuste_id'     => $notaAjuste->id,
                'id_salario'         => $salario->id_salario,
                'usuario_trabajador' => $notaAjuste->usuario_id,
                'aprobado_por'       => $adminDoc,
                'id_empresa'         => $notaAjuste->id_empresa,
                'periodo_id'         => $salario->id_periodo,
                'before'             => $before,
                'after'              => $updates,
                'timestamp'          => $ahora->toDateTimeString(),
            ]);

            return redirect()
                ->route('admin.notas-ajuste.show', $notaAjuste)
                ->with('success', 'Ajuste de pago aplicado correctamente al desprendible relacionado.');
        }

        // ── PATH B: sin detalles pre-guardados → comportamiento original ────────
        $request->merge([
            'ajuste' => $this->normalizeAdjustmentValues((array) $request->input('ajuste', [])),
        ]);

        $validated = $request->validateWithBag('ajustePago', [
            'campos_a_ajustar' => 'bail|required|array|min:1',
            'campos_a_ajustar.*' => 'bail|string|in:' . implode(',', self::CAMPOS_AJUSTABLES_PAGO),
            'ajuste' => 'bail|required|array',
            'ajuste.*' => 'nullable|numeric|min:0|max:999999999.99',
            'motivo_ajuste' => 'bail|required|string|min:8|max:500',
        ], [
            'campos_a_ajustar.required' => 'Debes seleccionar al menos un campo para ajustar.',
            'campos_a_ajustar.min' => 'Debes seleccionar al menos un campo para ajustar.',
            'campos_a_ajustar.*.in' => 'Uno de los campos seleccionados no está permitido para ajuste.',
            'ajuste.required' => 'Debes enviar los valores de ajuste.',
            'ajuste.*.numeric' => 'Los valores de ajuste deben ser numéricos.',
            'ajuste.*.min' => 'Los valores de ajuste no pueden ser negativos.',
            'ajuste.*.max' => 'Uno de los valores supera el límite permitido.',
            'motivo_ajuste.required' => 'Debes describir el motivo del ajuste.',
            'motivo_ajuste.min' => 'El motivo del ajuste debe tener al menos 8 caracteres.',
            'motivo_ajuste.max' => 'El motivo del ajuste no puede superar 500 caracteres.',
        ]);

        $camposSeleccionados = array_values(array_unique($validated['campos_a_ajustar'] ?? []));
        $detallesAjuste = [
            'hasData' => false,
            'rows' => [],
            'total' => 0.0,
            'total_horas_extra' => 0.0,
        ];

        $before = [];
        $updates = [];
        $detallesParaAuditoria = [];

        if (in_array('valor_horas_extras_recargos', $camposSeleccionados, true)) {
            $detallesAjuste = $this->buildDetalleHorasRecargosFromRequest($request, $salario);

            if ($detallesAjuste['hasData']) {
                $valorActualHorasRecargos = (float) ($salario->valor_horas_extras_recargos ?? 0);
                $valorActualHorasExtra = (float) ($salario->horas_extra ?? 0);

                $nuevoTotal = (float) $detallesAjuste['total'];
                $nuevoHorasExtra = (float) $detallesAjuste['total_horas_extra'];

                if (abs($valorActualHorasRecargos - $nuevoTotal) >= 0.00001) {
                    $before['valor_horas_extras_recargos'] = $valorActualHorasRecargos;
                    $updates['valor_horas_extras_recargos'] = $nuevoTotal;

                    $detallesParaAuditoria['valor_horas_extras_recargos'] = [
                        'valor_original'  => round($valorActualHorasRecargos, 2),
                        'valor_corregido' => round($nuevoTotal, 2),
                        'diferencia'      => round($nuevoTotal - $valorActualHorasRecargos, 2),
                        'es_salarial'     => true,
                        'guardado_por'    => $adminDoc,
                        'aprobado_por'    => $adminDoc,
                        'aprobado_en'     => $ahora,
                    ];
                }

                if (abs($valorActualHorasExtra - $nuevoHorasExtra) >= 0.00001) {
                    $before['horas_extra'] = $valorActualHorasExtra;
                    $updates['horas_extra'] = $nuevoHorasExtra;
                }

                $detallesAjuste['before_rows'] = HoraRecargoExtra::query()
                    ->where('id_salario', $salario->id_salario)
                    ->whereIn('id_tipo_hora_recargo', array_keys($detallesAjuste['rows']))
                    ->get(['id_tipo_hora_recargo', 'cantidad', 'pago'])
                    ->mapWithKeys(function ($row) {
                        return [
                            (int) $row->id_tipo_hora_recargo => [
                                'cantidad' => (float) $row->cantidad,
                                'pago' => (float) $row->pago,
                            ],
                        ];
                    })
                    ->toArray();
            }
        }

        foreach ($camposSeleccionados as $campo) {
            if ($campo === 'valor_horas_extras_recargos') {
                continue;
            }

            $nuevoValor = $validated['ajuste'][$campo] ?? null;

            if ($nuevoValor === null || $nuevoValor === '') {
                continue;
            }

            $valorActual = (float) ($salario->{$campo} ?? 0);
            $nuevoValor = (float) $nuevoValor;

            if (abs($valorActual - $nuevoValor) < 0.00001) {
                continue;
            }

            $before[$campo] = $valorActual;
            $updates[$campo] = $nuevoValor;

            $detallesParaAuditoria[$campo] = [
                'valor_original'  => round($valorActual, 2),
                'valor_corregido' => round($nuevoValor, 2),
                'diferencia'      => round($nuevoValor - $valorActual, 2),
                'es_salarial'     => in_array($campo, self::CAMPOS_SALARIALES, true),
                'guardado_por'    => $adminDoc,
                'aprobado_por'    => $adminDoc,
                'aprobado_en'     => $ahora,
            ];
        }

        if (empty($updates)) {
            return redirect()
                ->route('admin.notas-ajuste.show', $notaAjuste)
                ->with('warning', 'No se aplicaron cambios porque los valores enviados coinciden con los actuales o están vacíos.');
        }

        DB::transaction(function () use ($salario, $updates, $detallesAjuste, $notaAjuste, $detallesParaAuditoria) {
            $salario->update($updates);

            if (!empty($detallesAjuste['hasData'])) {
                foreach ($detallesAjuste['rows'] as $tipoId => $row) {
                    HoraRecargoExtra::query()->updateOrCreate(
                        [
                            'id_salario' => $salario->id_salario,
                            'id_tipo_hora_recargo' => (int) $tipoId,
                        ],
                        [
                            'cantidad' => (float) $row['cantidad'],
                            'pago' => (float) $row['pago'],
                        ]
                    );
                }
            }

            // Persistir detalles para trazabilidad
            foreach ($detallesParaAuditoria as $campo => $data) {
                NotaAjusteDetalle::updateOrCreate(
                    ['nota_id' => $notaAjuste->id, 'campo' => $campo],
                    $data
                );
            }
        });

        Log::info('Ajuste de pago aplicado desde nota de ajuste', [
            'nota_ajuste_id' => $notaAjuste->id,
            'id_salario' => $salario->id_salario,
            'usuario_trabajador' => $notaAjuste->usuario_id,
            'admin_doc' => $adminDoc,
            'id_empresa' => $notaAjuste->id_empresa,
            'periodo_id' => $salario->id_periodo,
            'periodo_estado' => $salario->periodo->estado ?? null,
            'motivo_ajuste' => trim($validated['motivo_ajuste']),
            'before' => $before,
            'after' => $updates,
            'detalle_horas_recargos_before' => $detallesAjuste['before_rows'] ?? [],
            'detalle_horas_recargos_after' => $detallesAjuste['rows'] ?? [],
            'timestamp' => $ahora->toDateTimeString(),
        ]);

        return redirect()
            ->route('admin.notas-ajuste.show', $notaAjuste)
            ->with('success', 'Ajuste de pago aplicado correctamente al desprendible relacionado.');
    }

    /**
     * Guarda las correcciones en nota_ajuste_detalles SIN tocar salario ni nómina.
     * Las correcciones quedan en estado "borrador" listas para la aprobación definitiva.
     */
    public function adminGuardarDetalles(Request $request, NotaAjuste $notaAjuste): RedirectResponse
    {
        $this->authorizeAdminAccess();

        abort_unless($this->canAccessAdminNote($notaAjuste), 403);

        $notaAjuste->loadMissing(['salario.periodo', 'salario.contrato']);

        abort_unless($notaAjuste->salario !== null, 404, 'No se encontró el desprendible asociado a la nota.');
        abort_if($notaAjuste->estado === NotaAjuste::ESTADO_OMITIDO, 422, 'No se pueden registrar correcciones sobre una nota omitida.');

        $request->merge([
            'ajuste' => $this->normalizeAdjustmentValues((array) $request->input('ajuste', [])),
        ]);

        $validated = $request->validateWithBag('guardarDetalles', [
            'campos_a_ajustar'   => 'bail|required|array|min:1',
            'campos_a_ajustar.*' => 'bail|string|in:' . implode(',', self::CAMPOS_AJUSTABLES_PAGO),
            'ajuste'             => 'bail|required|array',
            'ajuste.*'           => 'nullable|numeric|min:0|max:999999999.99',
        ], [
            'campos_a_ajustar.required'   => 'Debes seleccionar al menos un campo para guardar.',
            'campos_a_ajustar.min'        => 'Debes seleccionar al menos un campo para guardar.',
            'campos_a_ajustar.*.in'       => 'Uno de los campos seleccionados no está permitido.',
            'ajuste.required'             => 'Debes enviar los valores de corrección.',
            'ajuste.*.numeric'            => 'Los valores deben ser numéricos.',
            'ajuste.*.min'                => 'Los valores no pueden ser negativos.',
            'ajuste.*.max'                => 'Uno de los valores supera el límite permitido.',
        ]);

        $salario         = $notaAjuste->salario;
        $camposSeleccionados = array_values(array_unique($validated['campos_a_ajustar'] ?? []));
        $detalleHoras    = $this->buildDetalleHorasRecargosFromRequest($request, $salario);
        $adminDoc        = (string) (Auth::user()->doc ?? Auth::id());

        $detallesAGuardar = [];

        foreach ($camposSeleccionados as $campo) {
            if ($campo === 'valor_horas_extras_recargos') {
                if (!$detalleHoras['hasData']) {
                    continue;
                }
                $valorOriginal  = round((float) ($salario->valor_horas_extras_recargos ?? 0), 2);
                $valorCorregido = round((float) $detalleHoras['total'], 2);
            } else {
                $nuevoValor = $validated['ajuste'][$campo] ?? null;
                if ($nuevoValor === null || $nuevoValor === '') {
                    continue;
                }
                $valorOriginal  = round((float) ($salario->{$campo} ?? 0), 2);
                $valorCorregido = round((float) $nuevoValor, 2);
            }

            $diferencia = round($valorCorregido - $valorOriginal, 2);

            if (abs($diferencia) < 0.00001) {
                continue; // sin cambio real, no guardar
            }

            $detallesAGuardar[$campo] = [
                'valor_original'  => $valorOriginal,
                'valor_corregido' => $valorCorregido,
                'diferencia'      => $diferencia,
                'es_salarial'     => in_array($campo, self::CAMPOS_SALARIALES, true),
                'guardado_por'    => $adminDoc,
                'aprobado_por'    => null,
                'aprobado_en'     => null,
            ];
        }

        if (empty($detallesAGuardar)) {
            return redirect()
                ->route('admin.notas-ajuste.show', $notaAjuste)
                ->with('warning', 'No se guardaron correcciones porque los valores coinciden con los actuales o están vacíos.');
        }

        DB::transaction(function () use ($notaAjuste, $detallesAGuardar) {
            foreach ($detallesAGuardar as $campo => $data) {
                NotaAjusteDetalle::updateOrCreate(
                    ['nota_id' => $notaAjuste->id, 'campo' => $campo],
                    $data
                );
            }
        });

        Log::info('Correcciones guardadas en nota_ajuste_detalles', [
            'nota_ajuste_id' => $notaAjuste->id,
            'id_salario'     => $salario->id_salario,
            'guardado_por'   => $adminDoc,
            'campos'         => array_keys($detallesAGuardar),
            'timestamp'      => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('admin.notas-ajuste.show', $notaAjuste)
            ->with('success', 'Correcciones guardadas correctamente. Revisa el resumen antes de aplicar el ajuste definitivo.');
    }

    public function previewImpactoNota(Request $request, NotaAjuste $notaAjuste): JsonResponse
    {
        $this->authorizeAdminAccess();

        abort_unless($this->canAccessAdminNote($notaAjuste), 403);

        $notaAjuste->loadMissing(['salario.contrato', 'detalles']);

        if (!$notaAjuste->salario) {
            return response()->json(['sin_impacto' => true, 'mensaje' => 'Sin impacto económico']);
        }

        $salario          = $notaAjuste->salario;
        $totalDiferencia  = 0.0;
        $baseSeguridadSocial = 0.0;

        // ── PATH A: detalles ya guardados ──────────────────────────────────────
        $detallesGuardados = $notaAjuste->detalles;

        if ($detallesGuardados->isNotEmpty()) {
            foreach ($detallesGuardados as $detalle) {
                $diferencia = (float) $detalle->diferencia;

                if (in_array($detalle->campo, self::CAMPOS_DEDUCCION, true)) {
                    $totalDiferencia -= $diferencia;
                } else {
                    $totalDiferencia += $diferencia;
                    if ($detalle->es_salarial) {
                        $baseSeguridadSocial += $diferencia;
                    }
                }
            }

            $totalDiferencia     = round($totalDiferencia, 0);
            $baseSeguridadSocial = max(0.0, round($baseSeguridadSocial, 0));

            return response()->json([
                'sin_impacto'           => false,
                'total_diferencia'      => $totalDiferencia,
                'tipo'                  => $totalDiferencia >= 0 ? 'pago' : 'descuento',
                'base_seguridad_social' => $baseSeguridadSocial,
                'salud'                 => round($baseSeguridadSocial * 0.04, 0),
                'pension'               => round($baseSeguridadSocial * 0.04, 0),
                'fuente'                => 'guardado',
            ]);
        }

        // ── PATH B: sin detalles → usar request (comportamiento original) ──────
        $camposSeleccionados = array_values(
            array_unique(
                array_intersect(
                    (array) $request->input('campos_a_ajustar', []),
                    self::CAMPOS_AJUSTABLES_PAGO
                )
            )
        );

        if (empty($camposSeleccionados)) {
            return response()->json(['sin_impacto' => true, 'mensaje' => 'Sin impacto económico']);
        }

        $ajusteNormalizado = $this->normalizeAdjustmentValues((array) $request->input('ajuste', []));
        $detalleHoras      = $this->buildDetalleHorasRecargosFromRequest($request, $salario);

        foreach ($camposSeleccionados as $campo) {
            if ($campo === 'valor_horas_extras_recargos') {
                $nuevoValor = $detalleHoras['hasData']
                    ? (float) $detalleHoras['total']
                    : (float) ($ajusteNormalizado[$campo] ?? $salario->valor_horas_extras_recargos ?? 0);
            } else {
                if (!array_key_exists($campo, $ajusteNormalizado)) {
                    continue;
                }
                $nuevoValor = (float) $ajusteNormalizado[$campo];
            }

            $valorActual = (float) ($salario->{$campo} ?? 0);
            $diferencia  = $nuevoValor - $valorActual;

            if (in_array($campo, self::CAMPOS_DEDUCCION, true)) {
                $totalDiferencia -= $diferencia;
            } else {
                $totalDiferencia += $diferencia;
                if (in_array($campo, self::CAMPOS_SALARIALES, true)) {
                    $baseSeguridadSocial += $diferencia;
                }
            }
        }

        $totalDiferencia     = round($totalDiferencia, 0);
        $baseSeguridadSocial = max(0.0, round($baseSeguridadSocial, 0));

        return response()->json([
            'sin_impacto'           => false,
            'total_diferencia'      => $totalDiferencia,
            'tipo'                  => $totalDiferencia >= 0 ? 'pago' : 'descuento',
            'base_seguridad_social' => $baseSeguridadSocial,
            'salud'                 => round($baseSeguridadSocial * 0.04, 0),
            'pension'               => round($baseSeguridadSocial * 0.04, 0),
            'fuente'                => 'request',
        ]);
    }

    private function normalizeAdjustmentValues(array $values): array
    {
        $normalized = [];

        foreach ($values as $field => $value) {
            if (!is_scalar($value)) {
                $normalized[$field] = $value;
                continue;
            }

            $parsed = $this->parseLocalizedNumberString((string) $value);
            $normalized[$field] = $parsed ?? $value;
        }

        return $normalized;
    }

    private function parseLocalizedNumberString(string $value): ?string
    {
        $raw = trim($value);

        if ($raw === '') {
            return null;
        }

        $clean = str_replace(['$', ' ', ';'], ['', '', ','], $raw);
        $clean = preg_replace('/[^0-9,\.]/', '', $clean) ?? '';

        if ($clean === '') {
            return null;
        }

        $hasComma = str_contains($clean, ',');
        $hasDot = str_contains($clean, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($clean, ',');
            $lastDot = strrpos($clean, '.');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
        } elseif ($hasComma) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }

        return is_numeric($clean) ? $clean : null;
    }

    private function buildDetalleHorasRecargosFromRequest(Request $request, Salario $salario): array
    {
        $detallesRaw = (array) $request->input('detalle_recargos_visual', []);

        if (empty($detallesRaw)) {
            return [
                'hasData' => false,
                'rows' => [],
                'total' => 0.0,
                'total_horas_extra' => 0.0,
            ];
        }

        $ids = array_map('intval', array_keys($detallesRaw));
        $ids = array_values(array_filter($ids, fn($id) => $id > 0));

        if (empty($ids)) {
            return [
                'hasData' => false,
                'rows' => [],
                'total' => 0.0,
                'total_horas_extra' => 0.0,
            ];
        }

        $tipos = TipoHoraRecargo::query()
            ->whereIn('id_tipo_hora_recargo', $ids)
            ->get()
            ->keyBy('id_tipo_hora_recargo');

        $salarioBaseMensual = (float) ($salario->salario_base ?? ($salario->contrato->salario_base ?? 0));
        $valorHora = $salarioBaseMensual > 0 ? ($salarioBaseMensual / 240) : 0;

        $rows = [];
        $total = 0.0;
        $totalHorasExtra = 0.0;

        foreach ($detallesRaw as $rawId => $cantidadRaw) {
            $tipoId = (int) $rawId;
            if ($tipoId <= 0 || !$tipos->has($tipoId)) {
                continue;
            }

            $cantidad = $this->sanitizeHorasCantidad($cantidadRaw);
            $tipo = $tipos->get($tipoId);

            $rateFactor = $this->normalizeRateFactor((float) ($tipo->valor ?? 0));
            $nombre = mb_strtolower((string) ($tipo->nombre ?? ''), 'UTF-8');

            $factorAplicado = str_contains($nombre, 'extra') && $rateFactor < 1
                ? (1 + $rateFactor)
                : $rateFactor;

            $pago = round($cantidad * ($valorHora * $factorAplicado), 2);

            $rows[$tipoId] = [
                'cantidad' => $cantidad,
                'pago' => $pago,
                'nombre' => $tipo->nombre,
                'factor' => $factorAplicado,
            ];

            $total += $pago;

            if (str_contains($nombre, 'extra')) {
                $totalHorasExtra += $pago;
            }
        }

        return [
            'hasData' => !empty($rows),
            'rows' => $rows,
            'total' => round($total, 2),
            'total_horas_extra' => round($totalHorasExtra, 2),
        ];
    }

    private function sanitizeHorasCantidad($value): int
    {
        $cantidad = (int) floor((float) $value);

        if ($cantidad < 0) {
            return 0;
        }

        return min($cantidad, 744);
    }

    private function normalizeRateFactor(float $rate): float
    {
        if (!is_finite($rate) || $rate <= 0) {
            return 0.0;
        }

        if ($rate > 10) {
            return $rate / 100;
        }

        return $rate;
    }

    private function adminNotesQuery()
    {
        $query = NotaAjuste::query()->with('usuario');

        if ((int) Auth::user()->id_rol === 4) {
            return $query;
        }

        $empresaId = (int) session('empresa_id');

        if ($empresaId > 0) {
            $query->where('id_empresa', $empresaId);
        }

        return $query;
    }

    private function canAccessAdminNote(NotaAjuste $notaAjuste): bool
    {
        if ((int) Auth::user()->id_rol === 4) {
            return true;
        }

        $empresaId = (int) session('empresa_id');

        if ($empresaId <= 0) {
            return false;
        }

        return (int) $notaAjuste->id_empresa === $empresaId;
    }

    private function authorizeAdminAccess(): void
    {
        abort_if((int) Auth::user()->id_rol === 3, 403);
    }

    private function resolveCompanyId(string $doc): ?int
    {
        $sessionCompanyId = (int) session('empresa_id');
        if ($sessionCompanyId > 0) {
            return $sessionCompanyId;
        }

        $contractCompanyId = (int) Contrato::query()
            ->where('doc', $doc)
            ->orderByDesc('activo')
            ->orderByDesc('id_contrato')
            ->value('id_empresa');

        return $contractCompanyId > 0 ? $contractCompanyId : null;
    }
}