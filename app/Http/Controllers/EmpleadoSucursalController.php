<?php

namespace App\Http\Controllers;

use App\Models\EmpleadoSucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpleadoSucursalController extends Controller
{
    /**
     * Listar asignaciones.
     */
    public function index()
    {
        return response()->json(
            EmpleadoSucursal::with([
                'empleado',
                'sucursal'
            ])->get()
        );
    }

    /**
     * Crear asignación.
     */
    public function store(Request $request)
    {
        $rules = [

            'empleado_id' => [
                'required',
                'exists:empleados,id',
            ],

            'sucursal_id' => [
                'required',
                'exists:sucursales,id',
            ],

            'vigente_desde' => [
                'nullable',
                'date',
                Rule::unique('empleado_sucursal')
                    ->where(fn ($query) => $query
                        ->where('empleado_id', $request->empleado_id)
                        ->where('sucursal_id', $request->sucursal_id)
                    ),
            ],

            'vigente_hasta' => [
                'nullable',
                'date',
                'after_or_equal:vigente_desde',
            ],

            'modalidad' => [
                'required',
                Rule::in([
                    'presencial',
                    'home_office',
                    'hibrido',
                ]),
            ],

            'validar_gps' => 'boolean',

            'activo' => 'boolean',
        ];

        $datos = $request->validate($rules, [

            'vigente_desde.unique' =>
                'Ya existe una asignación para ese empleado, esa sucursal y esa fecha de inicio.',

            'vigente_hasta.after_or_equal' =>
                'La fecha de fin debe ser mayor o igual a la fecha de inicio.',

        ]);

        $empleadoSucursal = EmpleadoSucursal::create($datos);

       return response()->json([
            'message' => 'Asignación creada correctamente.',
            'data' => $empleadoSucursal,
        ], 201);
    }

    /**
     * Mostrar asignación.
     */
    public function show(EmpleadoSucursal $empleadoSucursal)
    {
        return response()->json(
            $empleadoSucursal->load([
                'empleado',
                'sucursal',
            ])
        );
    }

    /**
     * Actualizar asignación.
     */
    public function update(Request $request, EmpleadoSucursal $empleadoSucursal)
    {
        $empleadoId = $request->empleado_id ?? $empleadoSucursal->empleado_id;
        $sucursalId = $request->sucursal_id ?? $empleadoSucursal->sucursal_id;

        $rules = [

            'empleado_id' => [
                'sometimes',
                'exists:empleados,id',
            ],

            'sucursal_id' => [
                'sometimes',
                'exists:sucursales,id',
            ],

            'vigente_desde' => [
                'nullable',
                'date',
                Rule::unique('empleado_sucursal')
                    ->ignore($empleadoSucursal->id)
                    ->where(fn ($query) => $query
                        ->where('empleado_id', $empleadoId)
                        ->where('sucursal_id', $sucursalId)
                    ),
            ],

            'vigente_hasta' => [
                'nullable',
                'date',
                'after_or_equal:vigente_desde',
            ],

            'modalidad' => [
                Rule::in([
                    'presencial',
                    'home_office',
                    'hibrido',
                ]),
            ],

            'validar_gps' => 'boolean',

            'activo' => 'boolean',
        ];

        $datos = $request->validate($rules, [

            'vigente_desde.unique' =>
                'Ya existe una asignación para ese empleado, esa sucursal y esa fecha de inicio.',

            'vigente_hasta.after_or_equal' =>
                'La fecha de fin debe ser mayor o igual a la fecha de inicio.',

        ]);

        $empleadoSucursal->update($datos);

        return response()->json([
            'message' => 'Asignación actualizada correctamente.',
            'data' => $empleadoSucursal,
        ]);
    }

    /**
     * Eliminar asignación.
     */
    public function destroy(EmpleadoSucursal $empleadoSucursal)
    {
        $empleadoSucursal->delete();

        return response()->json([
            'message' => 'Asignación eliminada correctamente.'
        ]);
    }
}