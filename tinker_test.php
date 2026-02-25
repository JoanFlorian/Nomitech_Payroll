<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Requests\Step1Request;

// simulate an HTTP request via container
$req = Step1Request::create('/','POST',[
    'id_tipo_doc'=>1,
    'numero_documento'=>'1234567',
    'departamento'=>1,
    'ciudad'=>1,
    'primer_apellido'=>'Test',
    'primer_nombre'=>'Foo',
    'direccion'=>'Calle 123'
]);
$req->setContainer($app);
$req->setRedirector($app->make('redirect'));

// manually validate using the request rules/messages
$validator = \Validator::make($req->all(), $req->rules(), $req->messages());
$validator->validate();
$data = $validator->validated();
var_dump('validated', $data);
var_dump('all', $req->all());
