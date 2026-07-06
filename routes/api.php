<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\EmpleadoSucursalController;

use App\Http\Controllers\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| API Empresas
|--------------------------------------------------------------------------
|
| CRUD de empresas.
|
| GET      /api/empresas
| GET      /api/empresas/{empresa}
| POST     /api/empresas
| PUT      /api/empresas/{empresa}
| PATCH    /api/empresas/{empresa}
| DELETE   /api/empresas/{empresa}
|
*/
Route::apiResource('empresas', EmpresaController::class);

/*
|--------------------------------------------------------------------------
| API Empleados
|--------------------------------------------------------------------------
|
| CRUD de empleados.
|
| GET      /api/empleados
| GET      /api/empleados/{empleado}
| POST     /api/empleados
| PUT      /api/empleados/{empleado}
| PATCH    /api/empleados/{empleado}
| DELETE   /api/empleados/{empleado}
|
*/
Route::apiResource('empleados', EmpleadoController::class);

/*
|--------------------------------------------------------------------------
| API Sucursales
|--------------------------------------------------------------------------
|
| CRUD de sucursales.
|
| GET      /api/sucursales
| GET      /api/sucursales/{sucursal}
| POST     /api/sucursales
| PUT      /api/sucursales/{sucursal}
| PATCH    /api/sucursales/{sucursal}
| DELETE   /api/sucursales/{sucursal}
|
*/
Route::apiResource('sucursales', SucursalController::class);

/*
|--------------------------------------------------------------------------
| API Empleado - Sucursal
|--------------------------------------------------------------------------
|
| Administración de asignaciones de empleados a sucursales.
|
| GET      /api/empleado-sucursal
| GET      /api/empleado-sucursal/{empleadoSucursal}
| POST     /api/empleado-sucursal
| PUT      /api/empleado-sucursal/{empleadoSucursal}
| PATCH    /api/empleado-sucursal/{empleadoSucursal}
| DELETE   /api/empleado-sucursal/{empleadoSucursal}
|
*/
Route::apiResource('empleado-sucursal', EmpleadoSucursalController::class);


/*
|--------------------------------------------------------------------------
| API Empleado Dispositivos
|--------------------------------------------------------------------------
*/

Route::apiResource(
    'empleado-dispositivos',
    EmpleadoDispositivoController::class
);


/*
|--------------------------------------------------------------------------
| API Marcaciones
|--------------------------------------------------------------------------
*/
Route::apiResource(
    'marcaciones',
    MarcacionController::class
);

/*
|--------------------------------------------------------------------------
| API Telegram Webhook
|--------------------------------------------------------------------------
*/

Route::post('/telegram/webhook',TelegramWebhookController::class);

/*
|--------------------------------------------------------------------------
| API Feriados
|--------------------------------------------------------------------------
*/

Route::post(
    'feriados/importar',
    [FeriadosController::class, 'importarFeriados']
);

