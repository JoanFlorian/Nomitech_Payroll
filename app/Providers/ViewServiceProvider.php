<?php

namespace App\Providers;

use App\Models\Afp;
use App\Models\Arl;
use App\Models\CajaCompensacion;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Eps;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\NotaAjuste;
use App\Models\Pais;
use App\Models\SubTipoTrabajador;
use App\Models\TipoContrato;
use App\Models\TipoCuenta;
use App\Models\TipoDoc;
use App\Models\TipoTrabajador;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */


    public function boot(): void
    {
        View::composer('empleados.index', function ($view) {
            $pais = Pais::all();
            $departamento = Departamento::all();
            $ciudad = Ciudad::all();
            $tipodoc = TipoDoc::all();
            $tipotrabajadores = TipoTrabajador::all();
            $suptrabajadores = SubTipoTrabajador::all();
            $contratos = TipoContrato::all();
            $Arl = Arl::all();
            $formapagos = FormaPago::all();
            $metodopago = MetodoPago::all();
            $tipocuenta = TipoCuenta::all();
            $Eps = Eps::all();
            $Afp = Afp::all();
            $Cajas = CajaCompensacion::orderBy('nombre')->get();
            $view->with(compact('pais', 'departamento', 'ciudad', 'tipodoc', 'tipotrabajadores', 'suptrabajadores', 'contratos', 'Arl', 'formapagos', 'metodopago', 'tipocuenta', 'Eps', 'Afp', 'Cajas'));
        });

        View::composer('layouts.app', function ($view) {
            $count = 0;
            $items = collect();

            if (Auth::check() && (int) (Auth::user()->id_rol ?? 0) !== 3) {
                $query = NotaAjuste::query()
                    ->with(['usuario', 'salario.periodo'])
                    ->pendientes();

                if ((int) Auth::user()->id_rol !== 4) {
                    $empresaId = (int) session('empresa_id');
                    if ($empresaId > 0) {
                        $query->where('id_empresa', $empresaId);
                    }
                }

                $count = (clone $query)->count();
                $items = $query->latest()->limit(5)->get();
            }

            $view->with([
                'adminUnreadAdjustmentNotesCount' => $count,
                'adminUnreadAdjustmentNotes' => $items,
            ]);
        });



    }
}
