<?php

namespace App\Providers;

use App\Models\Afp;
use App\Models\Arl;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Eps;
use App\Models\FormaPago;
use App\Models\MetodoPago;
use App\Models\Pais;
use App\Models\SubTipoTrabajador;
use App\Models\TipoContrato;
use App\Models\TipoCuenta;
use App\Models\TipoDoc;
use App\Models\TipoTrabajador;
use App\Models\Usuario;
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
            $usuarios = Usuario::all();

            $view->with(compact('pais', 'departamento','ciudad','tipodoc','tipotrabajadores','suptrabajadores','contratos','Arl','formapagos','metodopago','tipocuenta','Eps','Afp','usuarios'));
        });



    }
}
