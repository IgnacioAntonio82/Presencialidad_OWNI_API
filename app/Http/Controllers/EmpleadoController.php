<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    /**
     * Listar empleados.
     */
    public function index()
    {
        return response()->json(
            Empleado::with(['empresa', 'sucursales'])->get()
        );
    }

    /**
     * Crear empleado.
     */
    public function store(Request $request)
    {

        $requiereCredenciales = $request->rol !== 'empleado';


        $rules = [

            'empresa_id' => [
                'required',
                'exists:empresas,id',
            ],

            'legajo' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9\-\/]+$/',
                Rule::unique('empleados')
                    ->where(fn ($query) => $query->where('empresa_id', $request->empresa_id)),
            ],

            'nombre' => 'required|string|max:255',

            'apellido' => 'required|string|max:255',

            'cuil' => [

                    'required',

                    'string',

                    'max:20',

                    Rule::unique('empleados')
                        ->where(fn ($query) => $query->where('empresa_id', $request->empresa_id)),

                    function ($attribute, $value, $fail) {

                        $cuil = preg_replace('/[^0-9]/', '', trim($value));

                        if (strlen($cuil) !== 11) {

                            $fail('El CUIL debe tener 11 dígitos.');

                            return;
                        }

                        if (!$this->validarCuil($cuil)) {

                            $fail('El CUIL ingresado no es válido.');
                        }
                    },

                ],

            'telefono' => 'nullable|string|max:50',

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('empleados')
                    ->where(fn ($query) => $query->where('empresa_id', $request->empresa_id)),
            ],

            'fecha_nacimiento' => 'nullable|date',

            'fecha_ingreso' => 'nullable|date',

            'fecha_egreso' => 'nullable|date|after_or_equal:fecha_ingreso',

            'usuario' => [
                $requiereCredenciales ? 'required' : 'nullable',
                'string',
                'max:100',
                Rule::unique('empleados')
                    ->where(fn ($query) => $query->where('empresa_id', $request->empresa_id)),
            ],

            'password' => [
                $requiereCredenciales ? 'required' : 'nullable',
                'string',
                'min:8',
            ],
            'rol' => [
                'required',
                Rule::in([
                    'empleado',
                    'supervisor',
                    'rrhh',
                    'admin',
                ]),
            ],

            'activo' => 'boolean',
        ];

        $datos = $request->validate($rules, [

            'legajo.regex' =>
                'El legajo solo puede contener letras, números, guiones y barras.',

            'legajo.unique' => 'El legajo ya existe en la empresa.',

            'cuil.unique' => 'El CUIL ya existe en la empresa.',

            'usuario.unique' => 'El usuario ya existe en la empresa.',

            'email.unique' => 'El correo electrónico ya existe en la empresa.',

            'fecha_egreso.after_or_equal' =>
                'La fecha de egreso debe ser mayor o igual a la fecha de ingreso.',
        ]);

        $datos['cuil'] = preg_replace(
            '/[^0-9]/',
            '',
            trim($datos['cuil'])
        );

        if (!empty($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        }

        $empleado = Empleado::create($datos);

        return response()->json([
            'message' => 'Empleado creado correctamente.',
            'data' => $empleado,
        ], 201);
    }

    /**
     * Mostrar empleado.
     */
    public function show(Empleado $empleado)
    {
        return response()->json(
            $empleado->load(['empresa', 'sucursales'])
        );
    }

    /**
     * Actualizar empleado.
     */
    public function update(Request $request, Empleado $empleado)
    {
        $empresaId = $request->empresa_id ?? $empleado->empresa_id;

        $rol = $request->rol ?? $empleado->rol;
        $requiereCredenciales = $rol !== 'empleado';

        $rules = [

            'empresa_id' => [
                'sometimes',
                'exists:empresas,id',
            ],

            'legajo' => [

                'sometimes',

                'string',

                'max:50',

                'regex:/^[A-Za-z0-9\-\/]+$/',

                Rule::unique('empleados')
                    ->ignore($empleado->id)
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),

            ],

            'nombre' => 'sometimes|string|max:255',

            'apellido' => 'sometimes|string|max:255',

            'cuil' => [

                'sometimes',

                'string',

                'max:20',

                Rule::unique('empleados')
                    ->ignore($empleado->id)
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),

                function ($attribute, $value, $fail) {

                    $cuil = preg_replace('/[^0-9]/', '', trim($value));

                    if (strlen($cuil) !== 11) {

                        $fail('El CUIL debe tener 11 dígitos.');

                        return;
                    }

                    if (!$this->validarCuil($cuil)) {

                        $fail('El CUIL ingresado no es válido.');
                    }
                },

            ],
            'telefono' => 'nullable|string|max:50',

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('empleados')
                    ->ignore($empleado->id)
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],

            'fecha_nacimiento' => 'nullable|date',

            'fecha_ingreso' => 'nullable|date',

            'fecha_egreso' => 'nullable|date|after_or_equal:fecha_ingreso',

            'usuario' => [
                $requiereCredenciales ? 'required' : 'nullable',
                'string',
                'max:100',
                Rule::unique('empleados')
                    ->ignore($empleado->id)
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],

            'password' => [
                $requiereCredenciales ? 'sometimes' : 'nullable',
                'string',
                'min:8',
            ],

            'rol' => [
                Rule::in([
                    'empleado',
                    'supervisor',
                    'rrhh',
                    'admin',
                ]),
            ],

            'activo' => 'boolean',
        ];

        $datos = $request->validate($rules, [

            'legajo.unique' => 'El legajo ya existe en la empresa.',

            'cuil.unique' => 'El CUIL ya existe en la empresa.',

            'usuario.unique' => 'El usuario ya existe en la empresa.',

            'email.unique' => 'El correo electrónico ya existe en la empresa.',

            'fecha_egreso.after_or_equal' =>
                'La fecha de egreso debe ser mayor o igual a la fecha de ingreso.',
        ]);

        if (isset($datos['cuil'])) {

            $datos['cuil'] = preg_replace(
                '/[^0-9]/',
                '',
                trim($datos['cuil'])
            );
        }

        if (isset($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        }

        $empleado->update($datos);

        return response()->json([
            'message' => 'Empleado actualizado correctamente.',
            'data' => $empleado,
        ]);
    }

    /**
     * Eliminar empleado.
     */
    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        return response()->json([
            'message' => 'Empleado eliminado correctamente.'
        ]);
    }


    /**
     * Validar CUIL/CUIT.
     */
    private function validarCuil(string $cuil): bool
    {
        if (!preg_match('/^\d{11}$/', $cuil)) {
            return false;
        }

        $multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];

        $suma = 0;

        for ($i = 0; $i < 10; $i++) {
            $suma += ((int) $cuil[$i]) * $multiplicadores[$i];
        }

        $resto = $suma % 11;

        $digito = 11 - $resto;

        if ($digito == 11) {
            $digito = 0;
        } elseif ($digito == 10) {
            $digito = 9;
        }

        return $digito == (int) $cuil[10];
    }
}