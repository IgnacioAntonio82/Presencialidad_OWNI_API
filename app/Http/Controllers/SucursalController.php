<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SucursalController extends Controller
{
    /**
     * Listar sucursales.
     */
    public function index()
    {
        return response()->json(
            Sucursal::with('empresa')->get()
        );
    }

    /**
     * Crear sucursal.
     */
    public function store(Request $request)
    {
        $rules = [

            'empresa_id' => [
                'required',
                'exists:empresas,id',
            ],

            'nombre' => 'required|string|max:255',

            'tipo_ubicacion' => [
                'required',
                Rule::in([
                    'empresa',
                    'cliente',
                    'obra',
                    'deposito',
                    'home_office',
                    'otro'
                ]),
            ],

            'direccion' => 'nullable|string|max:255',

            'localidad' => 'nullable|string|max:100',

            'provincia' => 'nullable|string|max:100',

            'codigo_postal' => 'nullable|string|max:20',

            'telefono' => 'nullable|string|max:50',

            'latitud' => 'nullable|numeric|between:-90,90',

            'longitud' => 'nullable|numeric|between:-180,180',

            'radio_permitido' => 'required|integer|min:1',

            'codigo_sucursal' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('sucursales')
                    ->where(fn ($query) => $query->where('empresa_id', $request->empresa_id)),
            ],

            'telegram_link' => 'nullable|url|max:255',

            'activo' => 'boolean',
        ];

        $datos = $request->validate($rules, [

            'codigo_sucursal.unique' =>
                'El código de sucursal ya existe en la empresa.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Obtener coordenadas automáticamente
        |--------------------------------------------------------------------------
        */

        if (
            empty($datos['latitud']) ||
            empty($datos['longitud'])
        ) {

            $direccionCompleta = implode(', ', array_filter([

                $datos['direccion'] ?? null,
                $datos['localidad'] ?? null,
                $datos['provincia'] ?? null,
                $datos['codigo_postal'] ?? null,
                'Argentina'

            ]));

            if (!empty($direccionCompleta)) {

                try {

                    $response = Http::withHeaders([

                        'User-Agent' => 'LaravelApp/1.0'

                    ])->timeout(10)->get(
                        'https://nominatim.openstreetmap.org/search',
                        [

                            'q' => $direccionCompleta,
                            'format' => 'json',
                            'limit' => 1,
                            'countrycodes' => 'ar',

                        ]
                    );

                    if (
                        $response->successful() &&
                        count($response->json()) > 0
                    ) {

                        $coordenadas = $response->json()[0];

                        $datos['latitud'] = $coordenadas['lat'];
                        $datos['longitud'] = $coordenadas['lon'];
                    }

                } catch (\Exception $e) {

                    $datos['latitud'] = null;
                    $datos['longitud'] = null;

                }

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Generar código de sucursal
        |--------------------------------------------------------------------------
        */

        if (empty($datos['codigo_sucursal'])) {

            do {

                $codigoSucursal = strtoupper(
                    Str::random(12)
                );

            } while (

                Sucursal::where(
                    'codigo_sucursal',
                    $codigoSucursal
                )->exists()

            );

            $datos['codigo_sucursal'] = $codigoSucursal;
        }

        /*
        |--------------------------------------------------------------------------
        | Generar enlace de Telegram
        |--------------------------------------------------------------------------
        */

        $datos['telegram_link'] =
            'https://t.me/Presencialidad_Owni_bot?start=' .
            $datos['codigo_sucursal'];



        $sucursal = Sucursal::create($datos);

        return response()->json([
            'message' => 'Sucursal creada correctamente.',
            'data' => $sucursal,
        ], 201);
    }

    /**
     * Mostrar sucursal.
     */
    public function show(Sucursal $sucursal)
    {
        return response()->json(
            $sucursal->load('empresa', 'empleados')
        );
    }

    /**
     * Actualizar sucursal.
     */
    public function update(Request $request, Sucursal $sucursal)
    {
        $empresaId = $request->empresa_id ?? $sucursal->empresa_id;

        $rules = [

            'empresa_id' => [
                'sometimes',
                'exists:empresas,id',
            ],

            'nombre' => 'sometimes|string|max:255',

            'tipo_ubicacion' => [
                Rule::in([
                    'empresa',
                    'cliente',
                    'obra',
                    'deposito',
                    'home_office',
                    'otro'
                ]),
            ],

            'direccion' => 'nullable|string|max:255',

            'localidad' => 'nullable|string|max:100',

            'provincia' => 'nullable|string|max:100',

            'codigo_postal' => 'nullable|string|max:20',

            'telefono' => 'nullable|string|max:50',

            'latitud' => 'nullable|numeric|between:-90,90',

            'longitud' => 'nullable|numeric|between:-180,180',

            'radio_permitido' => 'integer|min:1',

            'codigo_sucursal' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('sucursales')
                    ->ignore($sucursal->id)
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],

            'telegram_link' => 'nullable|url|max:255',

            'activo' => 'boolean',
        ];

        $datos = $request->validate($rules, [

            'codigo_sucursal.unique' =>
                'El código de sucursal ya existe en la empresa.',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Obtener coordenadas automáticamente
        |--------------------------------------------------------------------------
        */

        if (
            empty($datos['latitud']) ||
            empty($datos['longitud'])
        ) {

            $direccionCompleta = implode(', ', array_filter([

                $datos['direccion'] ?? $sucursal->direccion,
                $datos['localidad'] ?? $sucursal->localidad,
                $datos['provincia'] ?? $sucursal->provincia,
                $datos['codigo_postal'] ?? $sucursal->codigo_postal,
                'Argentina'

            ]));

            if (!empty($direccionCompleta)) {

                try {

                    $response = Http::withHeaders([

                        'User-Agent' => 'LaravelApp/1.0'

                    ])->timeout(10)->get(
                        'https://nominatim.openstreetmap.org/search',
                        [

                            'q' => $direccionCompleta,
                            'format' => 'json',
                            'limit' => 1,
                            'countrycodes' => 'ar',

                        ]
                    );

                    if (
                        $response->successful() &&
                        count($response->json()) > 0
                    ) {

                        $coordenadas = $response->json()[0];

                        $datos['latitud'] = $coordenadas['lat'];
                        $datos['longitud'] = $coordenadas['lon'];
                    }

                } catch (\Exception $e) {

                    $datos['latitud'] = null;
                    $datos['longitud'] = null;

                }

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Generar código de sucursal si no existe
        |--------------------------------------------------------------------------
        */

        if (
            empty($datos['codigo_sucursal']) &&
            empty($sucursal->codigo_sucursal)
        ) {

            do {

                $codigoSucursal = strtoupper(
                    Str::random(12)
                );

            } while (

                Sucursal::where(
                    'codigo_sucursal',
                    $codigoSucursal
                )->exists()

            );

            $datos['codigo_sucursal'] = $codigoSucursal;
        }

        /*
        |--------------------------------------------------------------------------
        | Actualizar enlace de Telegram
        |--------------------------------------------------------------------------
        */

        $codigoTelegram =
            $datos['codigo_sucursal']
            ?? $sucursal->codigo_sucursal;

        $datos['telegram_link'] =
            'https://t.me/Presencialidad_Owni_bot?start=' .
            $codigoTelegram;



        $sucursal->update($datos);

        return response()->json([
            'message' => 'Sucursal actualizada correctamente.',
            'data' => $sucursal,
        ]);
    }

    /**
     * Eliminar sucursal.
     */
    public function destroy(Sucursal $sucursal)
    {
        $sucursal->delete();

        return response()->json([
            'message' => 'Sucursal eliminada correctamente.'
        ]);
    }
}
