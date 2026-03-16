<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\NotaAjuste;
use App\Models\Salario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotaAjusteController extends Controller
{
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

        $notaAjuste->load(['usuario', 'salario.periodo', 'salario.contrato']);

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