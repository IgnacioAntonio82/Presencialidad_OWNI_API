<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    /**
     * Listar empresas.
     */
    public function index()
    {
        return response()->json(Empresa::all());
    }

    /**
     * Crear empresa.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'razon_social'   => 'required|string|max:255',
            'nombre_fantasia'=> 'nullable|string|max:255',
            'cuit' => [

                'required',

                'string',

                'max:20',

                'unique:empresas,cuit',

                function ($attribute, $value, $fail) {

                    $cuit = preg_replace(
                        '/[^0-9]/',
                        '',
                        trim($value)
                    );

                    if (strlen($cuit) !== 11) {

                        $fail('El CUIT debe tener 11 dígitos.');

                        return;
                    }

                    if (!$this->validarCuit($cuit)) {

                        $fail('El CUIT ingresado no es válido.');
                    }
                }
            ],
            'condicion_iva'  => 'nullable|string|max:100',
            'ingresos_brutos'=> 'nullable|string|max:100',
            'direccion'      => 'nullable|string|max:255',
            'localidad'      => 'nullable|string|max:100',
            'provincia'      => 'nullable|string|max:100',
            'codigo_postal'  => 'nullable|string|max:20',
            'fecha_inicio'   => 'nullable|date',
            'activo'         => 'boolean',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalizar CUIT
        |--------------------------------------------------------------------------
        */

        $datos['cuit'] = preg_replace(
            '/[^0-9]/',
            '',
            trim($datos['cuit'])
        );

        $empresa = Empresa::create($datos);

        return response()->json([
            'message' => 'Empresa creada correctamente.',
            'data' => $empresa,
        ], 201);
    }

    /**
     * Mostrar una empresa.
     */
    public function show(Empresa $empresa)
    {
        return response()->json($empresa);
    }

    /**
     * Actualizar empresa.
     */
    public function update(Request $request, Empresa $empresa)
    {
        $datos = $request->validate([
            'razon_social'   => 'sometimes|required|string|max:255',
            'nombre_fantasia'=> 'nullable|string|max:255',
            'cuit' => [

                'sometimes',

                'required',

                'string',

                'max:20',

                Rule::unique('empresas', 'cuit')
                    ->ignore($empresa->id),

                function ($attribute, $value, $fail) {

                    $cuit = preg_replace(
                        '/[^0-9]/',
                        '',
                        trim($value)
                    );

                    if (strlen($cuit) !== 11) {

                        $fail('El CUIT debe tener 11 dígitos.');

                        return;
                    }

                    if (!$this->validarCuit($cuit)) {

                        $fail('El CUIT ingresado no es válido.');
                    }
                }
            ],
            'condicion_iva'  => 'nullable|string|max:100',
            'ingresos_brutos'=> 'nullable|string|max:100',
            'direccion'      => 'nullable|string|max:255',
            'localidad'      => 'nullable|string|max:100',
            'provincia'      => 'nullable|string|max:100',
            'codigo_postal'  => 'nullable|string|max:20',
            'fecha_inicio'   => 'nullable|date',
            'activo'         => 'boolean',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Normalizar CUIT
        |--------------------------------------------------------------------------
        */

        if (isset($datos['cuit'])) {

            $datos['cuit'] = preg_replace(
                '/[^0-9]/',
                '',
                trim($datos['cuit'])
            );
        }

        $empresa->update($datos);

        return response()->json([
            'message' => 'Empresa actualizada correctamente.',
            'data' => $empresa,
        ]);
    }

    /**
     * Eliminar empresa.
     */
    public function destroy(Empresa $empresa)
    {
        $empresa->delete();

        return response()->json([
            'message' => 'Empresa eliminada correctamente.'
        ]);
    }


    /**
     * Validar CUIT/CUIL.
     */
    private function validarCuit(string $cuit): bool
    {
        if (!preg_match('/^\d{11}$/', $cuit)) {
            return false;
        }

        $multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];

        $suma = 0;

        for ($i = 0; $i < 10; $i++) {

            $suma += ((int) $cuit[$i]) * $multiplicadores[$i];
        }

        $resto = $suma % 11;

        $digito = 11 - $resto;

        if ($digito == 11) {
            $digito = 0;
        }

        if ($digito == 10) {
            $digito = 9;
        }

        return $digito == (int) $cuit[10];
    }



}
