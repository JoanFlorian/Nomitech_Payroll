<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $request = Illuminate\Http\Request::create('/provisiones/cesantias/consignacion-anual', 'POST', ['year' => 2026]);
    $request->setSession(app('session.store'));
    $request->session()->put('empresa_id', 1);

    $controller = app(App\Http\Controllers\ProvisionesController::class);
    $service = app(App\Services\Benefits\BenefitPaymentService::class);
    $response = $controller->generarConsignacionAnual($request, $service);

    if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
        copy($response->getFile()->getPathname(), 'test_consignacion.zip');
        echo "ZIP saved successfully to test_consignacion.zip";
    } else {
        echo "Response was: " . get_class($response);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
