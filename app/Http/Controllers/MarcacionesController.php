<?php

namespace App\Http\Controllers;

use App\Models\Marcaciones;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarcacionesController extends Controller
{
    /**
     * Listar marcaciones.
     */
    public function index()
    {
        return response()->json(

            Marcaciones::with([
                'empresa',
                'empleado',
                'sucursal',
                'dispositivo',
                'autorizador'
            ])->get()

        );
    }

    /**
     * Crear marcación.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([

            'empresa_id' => [
                'required',
                'exists:empresas,id'
            ],

            'empleado_id' => [
                'required',
                'exists:empleados,id'
            ],

            'empleado_dispositivo_id' => [
                'nullable',
                'exists:empleado_dispositivos,id'
            ],

            'empleado_autorizador_id' => [
                'nullable',
                'exists:empleados,id'
            ],

            'tipo' => [
                'required',
                Rule::in([
                    'ingreso',
                    'salida',
                    'almuerzo_inicio',
                    'almuerzo_fin',
                    'pausa_inicio',
                    'pausa_fin'
                ])
            ],

            'fecha' => [
                'required',
                'date'
            ],

            'fecha_hora' => [
                'required',
                'date'
            ],

            'latitud' => [
                'nullable',
                'numeric'
            ],

            'longitud' => [
                'nullable',
                'numeric'
            ],

            'origen' => [
                'sometimes',
                Rule::in([
                    'telegram',
                    'whatsapp',
                    'web',
                    'app',
                    'api'
                ])
            ],

            'es_manual' => [
                'sometimes',
                'boolean'
            ],

            'estado' => [
                'sometimes',
                Rule::in([
                    'pendiente',
                    'confirmada',
                    'rechazada'
                ])
            ],

            'id_dispositivo' => [
                'nullable',
                'string',
                'max:100'
            ],

            'motivo' => [
                'nullable',
                'string',
                'max:255'
            ],

            'notas' => [
                'nullable',
                'string'
            ]

        ]);

        $marcacion = Marcaciones::create($datos);

        return response()->json(

            $marcacion->load([
                'empresa',
                'empleado',
                'sucursal',
                'dispositivo',
                'autorizador'
            ]),

            201

        );
    }

    /**
     * Mostrar marcación.
     */
    public function show(Marcaciones $marcacion)
    {
        return response()->json(

            $marcacion->load([
                'empresa',
                'empleado',
                'sucursal',
                'dispositivo',
                'autorizador'
            ])

        );
    }

    /**
     * Actualizar marcación.
     */
    public function update(
        Request $request,
        Marcaciones $marcacion
    ) {

        $datos = $request->validate([

            'empresa_id' => [
                'sometimes',
                'exists:empresas,id'
            ],

            'empleado_id' => [
                'sometimes',
                'exists:empleados,id'
            ],

            'empleado_dispositivo_id' => [
                'nullable',
                'exists:empleado_dispositivos,id'
            ],

            'empleado_autorizador_id' => [
                'nullable',
                'exists:empleados,id'
            ],

            'tipo' => [
                'sometimes',
                Rule::in([
                    'ingreso',
                    'salida',
                    'almuerzo_inicio',
                    'almuerzo_fin',
                    'pausa_inicio',
                    'pausa_fin'
                ])
            ],

            'fecha' => [
                'sometimes',
                'date'
            ],

            'fecha_hora' => [
                'sometimes',
                'date'
            ],

            'latitud' => [
                'nullable',
                'numeric'
            ],

            'longitud' => [
                'nullable',
                'numeric'
            ],

            'origen' => [
                'sometimes',
                Rule::in([
                    'telegram',
                    'whatsapp',
                    'web',
                    'app',
                    'api'
                ])
            ],

            'es_manual' => [
                'sometimes',
                'boolean'
            ],

            'estado' => [
                'sometimes',
                Rule::in([
                    'pendiente',
                    'confirmada',
                    'rechazada'
                ])
            ],

            'id_dispositivo' => [
                'nullable',
                'string',
                'max:100'
            ],

            'motivo' => [
                'nullable',
                'string',
                'max:255'
            ],

            'notas' => [
                'nullable',
                'string'
            ]

        ]);

        $marcacion->update($datos);

        return response()->json(

            $marcacion->load([
                'empresa',
                'empleado',
                'sucursal',
                'dispositivo',
                'autorizador'
            ])

        );
    }

    /**
     * Eliminar marcación.
     */
    public function destroy(Marcaciones $marcacion)
    {
        $marcacion->delete();

        return response()->json([
            'message' => 'Marcación eliminada correctamente.'
        ]);
    }
}