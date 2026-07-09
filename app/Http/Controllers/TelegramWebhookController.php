<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Empleado;
use App\Models\EmpleadoDispositivo;
use App\Models\EmpleadoSucursal;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\Marcaciones;

use App\Models\EmpleadoHorario;
use Carbon\Carbon;




class TelegramWebhookController extends Controller
{

    private const ESTADOS = [

        'sin_jornada' => [
            '🟢 Ingresar'
        ],

        'ingreso' => [
            '🍽 Inicio Almuerzo',
            '☕ Inicio Pausa',
            '🔴 Salir'
        ],

        'almuerzo_inicio' => [
            '🍽 Fin Almuerzo'
        ],

        'almuerzo_fin' => [
            '☕ Inicio Pausa',
            '🔴 Salir'
        ],

        'pausa_inicio' => [
            '▶️ Fin Pausa'
        ],

        'pausa_fin' => [
            '🍽 Inicio Almuerzo',
            '☕ Inicio Pausa',
            '🔴 Salir'
        ],

        'salida' => [
            '🟢 Ingresar'
        ]

    ];

    private const TRANSICIONES = [

        'sin_jornada' => [
            'ingreso'
        ],

        'ingreso' => [
            'almuerzo_inicio',
            'pausa_inicio',
            'salida'
        ],

        'almuerzo_inicio' => [
            'almuerzo_fin'
        ],

        'almuerzo_fin' => [
            'pausa_inicio',
            'salida'
        ],

        'pausa_inicio' => [
            'pausa_fin'
        ],

        'pausa_fin' => [
            'almuerzo_inicio',
            'salida'
        ],

        'salida' => [
            'ingreso'
        ]

    ];

    private const MENSAJES = [

            /*
            |--------------------------------------------------------------------------
            | Sin jornada
            |--------------------------------------------------------------------------
            */

            'sin_jornada' => [

                'salida'            => '❌ Primero debe registrar el ingreso.',

                'almuerzo_inicio'   => '❌ Primero debe registrar el ingreso.',

                'almuerzo_fin'      => '❌ Primero debe registrar el ingreso.',

                'pausa_inicio'      => '❌ Primero debe registrar el ingreso.',

                'pausa_fin'         => '❌ Primero debe registrar el ingreso.'

            ],

            /*
            |--------------------------------------------------------------------------
            | Ingreso
            |--------------------------------------------------------------------------
            */

            'ingreso' => [

                'ingreso'           => '⚠️ El ingreso ya fue registrado.',

                'almuerzo_fin'      => '❌ Debe iniciar el almuerzo antes de finalizarlo.',

                'pausa_fin'         => '❌ Debe iniciar la pausa antes de finalizarla.'

            ],

            /*
            |--------------------------------------------------------------------------
            | Almuerzo iniciado
            |--------------------------------------------------------------------------
            */

            'almuerzo_inicio' => [

                'ingreso'           => '❌ Ya registró el ingreso.',

                'almuerzo_inicio'   => '⚠️ El almuerzo ya fue iniciado.',

                'pausa_inicio'      => '❌ No puede iniciar una pausa mientras está en almuerzo.',

                'pausa_fin'         => '❌ Debe finalizar el almuerzo antes de finalizar una pausa.',

                'salida'            => '❌ Primero debe finalizar el almuerzo.'

            ],

            /*
            |--------------------------------------------------------------------------
            | Almuerzo finalizado
            |--------------------------------------------------------------------------
            */

            'almuerzo_fin' => [

                'ingreso'           => '❌ Ya registró el ingreso.',

                'almuerzo_inicio'   => '⚠️ El almuerzo ya fue realizado.',

                'almuerzo_fin'      => '⚠️ El almuerzo ya fue finalizado.',

                'pausa_fin'         => '❌ Debe iniciar la pausa antes de finalizarla.'

            ],

            /*
            |--------------------------------------------------------------------------
            | Pausa iniciada
            |--------------------------------------------------------------------------
            */

            'pausa_inicio' => [

                'ingreso'           => '❌ Ya registró el ingreso.',

                'almuerzo_inicio'   => '❌ No puede iniciar el almuerzo durante una pausa.',

                'almuerzo_fin'      => '❌ No puede finalizar un almuerzo mientras está en pausa.',

                'pausa_inicio'      => '⚠️ La pausa ya fue iniciada.',

                'salida'            => '❌ Primero debe finalizar la pausa.'

            ],

            /*
            |--------------------------------------------------------------------------
            | Pausa finalizada
            |--------------------------------------------------------------------------
            */

            'pausa_fin' => [

                'ingreso'           => '❌ Ya registró el ingreso.',

                'almuerzo_fin'      => '❌ Debe iniciar el almuerzo antes de finalizarlo.',

                'pausa_inicio'      => '⚠️ La pausa ya fue utilizada.',

                'pausa_fin'         => '⚠️ La pausa ya fue finalizada.'

            ],

            /*
            |--------------------------------------------------------------------------
            | Jornada finalizada
            |--------------------------------------------------------------------------
            */

            'salida' => [

                'salida'            => '⚠️ La salida ya fue registrada.',

                'almuerzo_inicio'   => '❌ La jornada ya fue finalizada. Debe registrar un nuevo ingreso.',

                'almuerzo_fin'      => '❌ La jornada ya fue finalizada. Debe registrar un nuevo ingreso.',

                'pausa_inicio'      => '❌ La jornada ya fue finalizada. Debe registrar un nuevo ingreso.',

                'pausa_fin'         => '❌ La jornada ya fue finalizada. Debe registrar un nuevo ingreso.'

            ]

        ];

        private const ACCIONES = [

            '🟢 Ingresar' => 'ingreso',

            '🔴 Salir' => 'salida',

            '🍽 Inicio Almuerzo' => 'almuerzo_inicio',

            '🍽 Fin Almuerzo' => 'almuerzo_fin',

            '☕ Inicio Pausa' => 'pausa_inicio',

            '▶️ Fin Pausa' => 'pausa_fin'

        ];

    private const MENSAJES_MARCACION = [

        'ingreso' => [
            'titulo'  => '🟢 Ingreso registrado',
            'mensaje' => '¡Bienvenido! Que tenga una excelente jornada laboral.'
        ],

        'salida' => [
            'titulo'  => '🔴 Salida registrada',
            'mensaje' => 'Su jornada laboral ha finalizado correctamente.'
        ],

        'almuerzo_inicio' => [
            'titulo'  => '🍽 Inicio de almuerzo registrado',
            'mensaje' => 'Buen provecho.'
        ],

        'almuerzo_fin' => [
            'titulo'  => '🍽 Fin de almuerzo registrado',
            'mensaje' => 'Puede continuar con su jornada laboral.'
        ],

        'pausa_inicio' => [
            'titulo'  => '☕ Inicio de pausa registrado',
            'mensaje' => 'Disfrute su descanso.'
        ],

        'pausa_fin' => [
            'titulo'  => '▶️ Fin de pausa registrado',
            'mensaje' => 'Puede continuar con sus tareas.'
        ]

    ];    


    public function __invoke(Request $request)
    {
        try {
        $update = $request->all();

        if (!isset($update['message'])) {
            return response()->json(['ok' => true]);
        }

        $message = $update['message'];

        $chatId = $message['chat']['id'];

        // $this->sendMessage(
        //     $chatId,
        //     "Update: {$update['update_id']}\n" .
        //     "Texto: " . ($message['text'] ?? 'SIN TEXTO') . "\n" .
        //     "Location: " . (isset($message['location']) ? 'SI' : 'NO')
        // );

        $texto = trim($message['text'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | Buscar dispositivo registrado
        |--------------------------------------------------------------------------
        */

        $dispositivo = EmpleadoDispositivo::where('tipo', 'telegram')
            ->where('identificador', $chatId)
            ->where('activo', true)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Usuario registrado
        |--------------------------------------------------------------------------
        */

        if ($dispositivo) {

            $empleado = Empleado::find($dispositivo->empleado_id);
            

            if (!$empleado || !$empleado->activo) {

                $this->sendMessage(
                    $chatId,
                    "🚫 Su usuario se encuentra inactivo."
                );

                return response()->json([
                    'ok' => true
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | /start usuario registrado
            |--------------------------------------------------------------------------
            */

            if ($texto === '/start') {

                $this->sendMessage(
                    $chatId,
                    "👋 Bienvenido nuevamente {$empleado->nombre}."
                );

                $this->enviarMenu($chatId, $empleado);

                return response()->json(['ok' => true]);
            }


            /*
            |--------------------------------------------------------------------------
            | Recibió una ubicación
            |--------------------------------------------------------------------------
            */
                      

            if (isset($message['location'])) {

            //    $key = "marcacion:$chatId";

            //     $this->sendMessage(
            //         $chatId,
            //         "HAS: " . (Cache::has($key) ? "SI" : "NO")
            //     );

            //     $accion = Cache::get($key);

            //     $this->sendMessage(
            //         $chatId,
            //         "GET: " . json_encode($accion)
            //     );

            //     return response()->json(['ok' => true]);

            //$accion = Cache::get("marcacion:$chatId");
            $accion = Cache::pull("marcacion:$chatId");

            if (!$accion) {

                $this->sendMessage(
                    $chatId,
                    "❌ No existe ninguna marcación pendiente."
                );

                $this->enviarMenu(
                    $chatId,
                    $empleado
                );

                return response()->json([
                    'ok' => true
                ]);
            }

            $latitud = $message['location']['latitude'];

            $longitud = $message['location']['longitude'];

            $sucursalEmpleado = $this->obtenerSucursalActiva(
                $empleado->id
            );

            if (!$sucursalEmpleado) {

                Cache::forget("marcacion:$chatId");

                $this->sendMessage(
                    $chatId,
                    "❌ No tiene una sucursal activa asignada."
                );

                return response()->json(['ok' => true]);
            }

            $validacion = $this->validarUbicacion(

                $sucursalEmpleado,

                $latitud,

                $longitud

            );

            if (!$validacion['ok']) {

                Cache::forget("marcacion:$chatId");

                $this->sendMessage(

                    $chatId,

                    $validacion['mensaje']

                );

                $this->enviarMenu(
                    $chatId,
                    $empleado
                );

                return response()->json([
                    'ok' => true
                ]);
            }

            $ok =$this->registrarMarcacion(

                $empleado,

                $dispositivo,

                $accion['tipo'],

                $latitud,

                $longitud,

                $validacion['sucursal_id']

            );

            if (!$ok) {

                Cache::forget("marcacion:$chatId");

                return response()->json([
                    'ok' => true
                ]);

            }

            //Cache::forget("marcacion:$chatId");

            $sucursal = $this->obtenerSucursalActiva(
                $empleado->id
            );

            $this->sendMessage(

                $chatId,

                $this->obtenerMensajeMarcacion(
                    $accion['tipo'],
                    $empleado,
                    $sucursal?->sucursal
                )

            );
            

            $this->enviarMenu(
                $chatId,
                $empleado
            );

            return response()->json([
                'ok' => true
            ]);
        }

                       
           

        if (isset(self::ACCIONES[$texto])) {

            //$accion = self::ACCIONES[$texto];

            $this->sendMessage($chatId, "PASO 1");

            $accion = self::ACCIONES[$texto];

            $this->sendMessage($chatId, "PASO 2");


            $sucursalEmpleado = $this->obtenerSucursalActiva(
                $empleado->id
            );

            $this->sendMessage($chatId, "PASO 3");

            if (!$sucursalEmpleado) {

                $this->sendMessage($chatId, "NO TIENE SUCURSAL");

                $this->sendMessage(
                    $chatId,
                    "❌ No tiene una sucursal activa asignada."
                );

                return response()->json([
                    'ok' => true
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar secuencia
            |--------------------------------------------------------------------------
            */
            $this->sendMessage($chatId, "PASO 4");

            $validacion = $this->validarSecuencia(
                $empleado->id,
                $accion
            );

            $this->sendMessage($chatId, "PASO 5");

            if (!$validacion['ok']) {

                $this->sendMessage(
                    $chatId,
                    $validacion['mensaje']
                );

                return response()->json([
                    'ok' => true
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar horario
            |--------------------------------------------------------------------------
            */

            $horario = $this->validarHorarioLaboral($empleado);

            $this->sendMessage($chatId, "PASO 6");

            if (!$horario['ok']) {

                $this->sendMessage(
                    $chatId,
                    $horario['mensaje']
                );

                return response()->json([
                    'ok' => true
                ]);
            }



            /*
            |--------------------------------------------------------------------------
            | NO valida GPS
            |--------------------------------------------------------------------------
            */

            $this->sendMessage($chatId, "PASO 7");

            $this->sendMessage(
                $chatId,
                "Modalidad: {$sucursalEmpleado->modalidad} - GPS: " .
                ($sucursalEmpleado->validar_gps ? 'SI' : 'NO')
            );

            if (
                $sucursalEmpleado->modalidad === 'home_office' ||
                !$sucursalEmpleado->validar_gps
            ) {

                $this->sendMessage($chatId, "PASO HOME");

                $this->sendMessage($chatId, "Antes de registrar");
                $ok =$this->registrarMarcacion(

                    $empleado,

                    $dispositivo,

                    $accion,

                    null,

                    null,

                    $sucursalEmpleado->sucursal_id

                );
                $this->sendMessage($chatId, "Resultado: " . ($ok ? "TRUE" : "FALSE"));

                if (!$ok) {

                    return response()->json([
                        'ok' => true
                    ]);
                }

                $sucursal = $this->obtenerSucursalActiva(
                    $empleado->id
                );

                $this->sendMessage(

                    $chatId,

                    $this->obtenerMensajeMarcacion(
                        $accion,
                        $empleado,
                        $sucursal?->sucursal
                    )

                );
                

                $this->enviarMenu(
                    $chatId,
                    $empleado
                );

                return response()->json([
                    'ok' => true
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | SI valida GPS
            |--------------------------------------------------------------------------
            */
           
            $this->sendMessage($chatId, "PASO GPS");
            Cache::put(

                "marcacion:$chatId",

                [

                    'tipo' => $accion,

                    'sucursal_id' => $sucursalEmpleado->sucursal_id

                ],

                now()->addMinutes(2)

            );

            $this->sendMessage($chatId, "PASO CACHE");

                $this->enviarBotonUbicacion($chatId);

                $this->sendMessage($chatId, "PASO BOTON");

            

            $this->enviarBotonUbicacion($chatId);

            return response()->json([
                'ok' => true
            ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Cualquier otro mensaje
            |--------------------------------------------------------------------------
            */

            $this->enviarMenu($chatId, $empleado);

            return response()->json(['ok' => true]);
        }

        


        /*
        |--------------------------------------------------------------------------
        | /start ABC123
        |--------------------------------------------------------------------------
        */

        if (str_starts_with($texto, '/start')) {

        /*
        |--------------------------------------------------------------------------
        | El dispositivo ya está registrado
        |--------------------------------------------------------------------------
        */

        

        /*
        |--------------------------------------------------------------------------
        | Debe venir con el código QR
        |--------------------------------------------------------------------------
        */

            $partes = explode(' ', $texto);

            if (!isset($partes[1])) {

                $this->sendMessage(
                    $chatId,
                    "📷 Escanee el código QR de la sucursal para vincular su dispositivo."
                );

                return response()->json(['ok' => true]);
            }

            $codigoSucursal = $partes[1];

            $sucursal = Sucursal::where(
                'codigo_sucursal',
                $codigoSucursal
            )->first();

            if (!$sucursal) {

                $this->sendMessage(
                    $chatId,
                    "🏢❌ La sucursal indicada no existe."
                );

                return response()->json(['ok' => true]);
            }

            Cache::put(

                "telegram:$chatId",

                [
                    'paso' => 'legajo',
                    'empresa_id' => $sucursal->empresa_id,
                    'sucursal_id' => $sucursal->id
                ],

                now()->addMinutes(30)

            );

            $this->sendMessage(

                $chatId,

                "👋 Bienvenido.\n\n" .
                "🏢 Sucursal: {$sucursal->nombre}\n\n" .
                "🆔 Ingrese su legajo."

            );

            return response()->json(['ok' => true]);
        }






        /*
        |--------------------------------------------------------------------------
        | Conversación de vinculación
        |--------------------------------------------------------------------------
        */

        if (Cache::has("telegram:$chatId")) {

            $estado = Cache::get("telegram:$chatId");

            /*
            |--------------------------------------------------------------------------
            | Paso: Legajo
            |--------------------------------------------------------------------------
            */

            if ($estado['paso'] == 'legajo') {

                $empleado = Empleado::where(
                        'empresa_id',
                        $estado['empresa_id']
                    )
                    ->where(
                        'legajo',
                        $texto
                    )
                    ->where(
                        'activo',
                        true
                    )
                    ->first();

                if (!$empleado) {

                    $this->sendMessage(
                        $chatId,
                        "❌ Legajo incorrecto.\n\n🆔 Ingrese nuevamente su legajo."
                    );

                    return response()->json(['ok' => true]);
                }

                $estado['paso'] = 'cuil';
                $estado['empleado_id'] = $empleado->id;

                Cache::put(

                    "telegram:$chatId",

                    $estado,

                    now()->addMinutes(30)

                );

                $this->sendMessage(
                    $chatId,
                    "🔐 Ingrese su CUIL."
                );

                return response()->json(['ok' => true]);
            }

            /*
            |--------------------------------------------------------------------------
            | Paso: CUIL
            |--------------------------------------------------------------------------
            */

            if ($estado['paso'] == 'cuil') {

                $empleado = Empleado::find(
                    $estado['empleado_id']
                );

                if ($empleado->cuil != $texto) {

                    $this->sendMessage(
                        $chatId,
                        "❌ CUIL incorrecto.\n\n🔐 Ingrese nuevamente su CUIL."
                    );

                    return response()->json(['ok' => true]);
                }

                $autorizado = EmpleadoSucursal::where(
                        'empleado_id',
                        $empleado->id
                    )
                    ->where(
                        'sucursal_id',
                        $estado['sucursal_id']
                    )
                    ->where(
                        'activo',
                        true
                    )
                    ->exists();

                if (!$autorizado) {

                    Cache::forget("telegram:$chatId");

                    $this->sendMessage(
                        $chatId,
                        "🚫 No está autorizado para trabajar en esta sucursal."
                    );

                    return response()->json(['ok' => true]);
                }

                

                $yaRegistrado = EmpleadoDispositivo::where(
                        'empresa_id',
                        $empleado->empresa_id
                    )
                    ->where(
                        'empleado_id',
                        $empleado->id
                    )
                    ->where(
                        'tipo',
                        'telegram'
                    )
                    ->where(
                        'identificador',
                        $chatId
                    )
                    ->exists();

                if ($yaRegistrado) {

                    Cache::forget("telegram:$chatId");

                    $this->sendMessage(
                        $chatId,
                        "✅ Este dispositivo ya se encuentra registrado."
                    );

                    return response()->json(['ok' => true]);
                }

                // Si no existe, updateOrCreate hará el alta o actualizará el chat_id.
                EmpleadoDispositivo::updateOrCreate(
                    [
                        'empresa_id' => $empleado->empresa_id,
                        'empleado_id' => $empleado->id,
                        'tipo' => 'telegram'
                    ],
                    [
                        'identificador' => $chatId,
                        'nombre' => trim(
                            ($message['from']['first_name'] ?? '') . ' ' .
                                    ($message['from']['last_name'] ?? '')
                                ),
                                'celular' => $message['contact']['phone_number'] ?? null,
                                'activo' => true
                            ]
                        );



                Cache::forget("telegram:$chatId");

                $this->sendMessage(
                    $chatId,
                    "✅ Dispositivo registrado correctamente.\n\nBienvenido {$empleado->nombre}."
                );

                $this->enviarMenu($chatId, $empleado);

                return response()->json(['ok' => true]);
            }
        }

        $this->sendMessage(
            $chatId,
            "🤖 Comando no reconocido.\n\n"             
            );

        return response()->json(['ok' => true]);
     } catch (\Throwable $e) {

        \Log::error('TELEGRAM ERROR', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

        return response()->json([
            'ok' => true
        ]);
    }
}

    /**
     * Envía un mensaje a Telegram.
     */
    private function sendMessage(int|string $chatId, string $texto): void
    {
        $response = Http::post(

            'https://api.telegram.org/bot'
            . env('TELEGRAM_BOT_TOKEN')
            . '/sendMessage',

            [
                'chat_id' => $chatId,
                'text' => $texto
            ]

        );

        if (!$response->successful()) {

            \Log::error('Error enviando mensaje a Telegram', [

                'status' => $response->status(),

                'body' => $response->body(),

                'chat_id' => $chatId,

                'texto' => $texto

            ]);
        }
    }

    private function enviarBotonUbicacion($chatId): void
    {
        $response = Http::post(

            'https://api.telegram.org/bot'
            . env('TELEGRAM_BOT_TOKEN')
            . '/sendMessage',

            [

                'chat_id' => $chatId,

                'text' => '📍 Para registrar la marcación debe compartir su ubicación.',

                'reply_markup' => [

                    'keyboard' => [

                        [

                            [

                                'text' => '📍 Compartir ubicación',

                                'request_location' => true

                            ]

                        ]

                    ],

                    'resize_keyboard' => true,

                    'one_time_keyboard' => true,

                    'is_persistent' => false

                ]

            ]

        );

        if (!$response->successful()) {

            \Log::error('Error enviando botón de ubicación', [

                'status' => $response->status(),

                'body' => $response->body(),

                'chat_id' => $chatId

            ]);

        }
    }

    private function obtenerSucursalActiva($empleadoId)
    {
        return EmpleadoSucursal::with('sucursal')

            ->whereHas('sucursal', function ($q) {
                $q->where('activo', true);
            })

            ->where('empleado_id', $empleadoId)

            ->where('activo', true)

            ->where(function ($q) {
                $q->whereNull('vigente_desde')
                ->orWhere('vigente_desde', '<=', today());
            })

            ->where(function ($q) {
                $q->whereNull('vigente_hasta')
                ->orWhere('vigente_hasta', '>=', today());
            })

            ->orderByDesc('vigente_desde')

            ->first();
    }

    private function calcularDistancia(  float $lat1,  float $lon1, float $lat2,  float $lon2): float
    {
        $radioTierra = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $radioTierra * $c;
    }

    private function validarUbicacion(  $sucursalEmpleado,  $latitud,  $longitud): array
    {
        $sucursal = $sucursalEmpleado->sucursal;

        if (!$sucursal) {

            return [

                'ok' => false,

                'mensaje' => '❌ La sucursal no existe.'

            ];
        }

        if (
            is_null($sucursal->latitud) ||
            is_null($sucursal->longitud)
        ) {

            return [

                'ok' => false,

                'mensaje' => '❌ La sucursal no posee coordenadas GPS.'

            ];
        }

        $distancia = $this->calcularDistancia(

            $latitud,

            $longitud,

            $sucursal->latitud,

            $sucursal->longitud

        );

        if ($distancia > $sucursal->radio_permitido) {

            return [

                'ok' => false,

                'mensaje' =>
                    "❌ Está fuera del radio permitido.\n\n" .
                    "📍 Distancia: "
                    . round($distancia)
                    . " metros.\n" .
                    "📌 Radio permitido: "
                    . $sucursal->radio_permitido
                    . " metros."

            ];
        }

        return [

            'ok' => true,

            'sucursal_id' => $sucursal->id

        ];
    }




    private function obtenerUltimaMarcacion($empleadoId)
    {
        return Marcaciones::where(
                'empleado_id',
                $empleadoId
            )
            ->whereDate(
                'fecha',
                today()
            )
            ->orderByDesc('fecha_hora')
            ->first();
    }

    private function crearKeyboard($estado)
    {

        $botones = self::ESTADOS[$estado] ?? self::ESTADOS['sin_jornada'];

    $keyboard = [];

    foreach ($botones as $boton) {

        $keyboard[] = [

            [

                'text' => $boton

            ]

        ];

    }

    return $keyboard;

}

    private function obtenerEstadoActual($empleadoId)
    {

        $ultima = $this->obtenerUltimaMarcacion(
            $empleadoId
        );

        if (!$ultima) {

            return 'sin_jornada';

        }

        return match ($ultima->tipo) {

            'ingreso' => 'ingreso',

            'almuerzo_inicio' => 'almuerzo_inicio',

            'almuerzo_fin' => 'almuerzo_fin',

            'pausa_inicio' => 'pausa_inicio',

            'pausa_fin' => 'pausa_fin',

            'salida' => 'salida',

            default => 'sin_jornada'

        };

    }

    private function validarSecuencia($empleadoId, $tipo): array
        {
            $estado = $this->obtenerEstadoActual($empleadoId);

            $permitidos = self::TRANSICIONES[$estado] ?? [];

            if (!in_array($tipo, $permitidos)) {

                return [

                    'ok' => false,

                    'mensaje' => self::MENSAJES[$estado][$tipo]
                        ?? '❌ La acción solicitada no está permitida.'

                ];
            }

            return [

                'ok' => true

            ];
        }

    private function enviarMenu($chatId, $empleado): void
    {
        $estado = $this->obtenerEstadoActual(
            $empleado->id
        );

        $response = Http::post(

            'https://api.telegram.org/bot'
            . env('TELEGRAM_BOT_TOKEN')
            . '/sendMessage',

            [

                'chat_id' => $chatId,

                'text' => 'Seleccione una acción:',

                'reply_markup' => [

                    'keyboard' => $this->crearKeyboard($estado),

                    'resize_keyboard' => true,

                    'one_time_keyboard' => false,

                    'is_persistent' => true

                ]

            ]

        );

        if (!$response->successful()) {

            \Log::error('Error enviando menú de Telegram', [

                'status' => $response->status(),

                'body' => $response->body(),

                'chat_id' => $chatId,

                'empleado_id' => $empleado->id,

                'estado' => $estado

            ]);

        }
    }

    private function obtenerHorarioEmpleado($empleado)
    {
        $sucursalEmpleado = $this->obtenerSucursalActiva($empleado->id);

        if (!$sucursalEmpleado) {
            return null;
        }

        return EmpleadoHorario::with('horario')

            ->where('empresa_id', $empleado->empresa_id)

            ->where('empleado_id', $empleado->id)

            ->where('activo', true)

            ->whereDate(
                'vigente_desde',
                '<=',
                today()
            )

            ->where(function ($q) {

                $q->whereNull('vigente_hasta')

                ->orWhereDate(
                        'vigente_hasta',
                        '>=',
                        today()
                );

            })

            ->whereHas('horario', function ($q) use ($sucursalEmpleado) {

                $q->where('activo', true)
                ->where('sucursal_id', $sucursalEmpleado->sucursal_id);

            })

            ->first();
    }



    private function validarHorarioLaboral($empleado): array
    {
        $empleadoHorario = $this->obtenerHorarioEmpleado( $empleado );

        if (!$empleadoHorario) {

            return [

                'ok' => false,

                'mensaje' => '❌ No posee un horario laboral asignado.'

            ];

        }

        $horario = $empleadoHorario->horario;

        /*
        |--------------------------------------------------------------------------
        | Día de la semana
        |--------------------------------------------------------------------------
        */

        $dias = [

            1 => 'lunes',

            2 => 'martes',

            3 => 'miercoles',

            4 => 'jueves',

            5 => 'viernes',

            6 => 'sabado',

            7 => 'domingo'

        ];

        $dia = $dias[now()->dayOfWeekIso];

        if (!$horario->$dia) {

            return [

                'ok' => false,

                'mensaje' => '❌ Hoy no posee una jornada laboral asignada.'

            ];

        }

        /*
        |--------------------------------------------------------------------------
        | Horario laboral
        |--------------------------------------------------------------------------
        */

        $horaActual = Carbon::now()->format('H:i:s');

        if (           

            $horaActual > $horario->hora_salida

        ) {

            return [

                'ok' => false,

               'mensaje' =>
                    "❌ La marcación sólo puede realizarse hasta las "
                    . substr($horario->hora_salida, 0, 5)

            ];

        }

        return [

            'ok' => true,

            'horario' => $horario

        ];

    }



    private function registrarMarcacion(  $empleado, $dispositivo, $tipo,  $latitud = null,  $longitud = null,  $sucursalId = null ):bool
    {
        
        $ultima = $this->obtenerUltimaMarcacion(
            $empleado->id
        );

        /*
        |--------------------------------------------------------------------------
        | Evitar duplicados
        |--------------------------------------------------------------------------
        */

        if ($ultima && $ultima->tipo == $tipo) {

            $this->sendMessage(
                $dispositivo->identificador,
                "⚠️ Esa marcación ya fue registrada."
            );

            return false;
        }
        

        try{

        Marcaciones::create([

            'empresa_id' => $empleado->empresa_id,

            'empleado_id' => $empleado->id,

            'empleado_dispositivo_id' => $dispositivo->id,

            'sucursal_id' => $sucursalId,

            'tipo' => $tipo,

            'fecha' => today(),

            'fecha_hora' => now(),

            'latitud' => $latitud,

            'longitud' => $longitud,

            'origen' => 'telegram',

            'estado' => 'confirmada'

        ]);
        

         $this->sendMessage(
        $dispositivo->identificador,
        "Registro insertado correctamente"
    );

    return true;

        } catch (\Throwable $e) {
            $this->sendMessage(
                $dispositivo->identificador,
                $e->getMessage());
            
        }
               

    } 
    
    
   private function obtenerMensajeMarcacion(  $tipo,  $empleado,  $sucursal = null): string
    {
        $datos = self::MENSAJES_MARCACION[$tipo];

        $texto = $datos['titulo'];

        $texto .= "\n\n";

        $texto .= "👤 {$empleado->apellido}, {$empleado->nombre}";

        if ($sucursal) {

            $texto .= "\n🏢 {$sucursal->nombre}";

        }

        $texto .= "\n🕒 " . now()->format('d/m/Y H:i:s');

        /*
        |--------------------------------------------------------------------------
        | Duración del almuerzo
        |--------------------------------------------------------------------------
        */

        if ($tipo == 'almuerzo_fin') {

            $inicio = Marcaciones::where('empleado_id', $empleado->id)
                ->whereDate('fecha', today())
                ->where('tipo', 'almuerzo_inicio')
                ->latest('fecha_hora')
                ->first();

            if ($inicio) {

                $minutos = $inicio->fecha_hora->diffInMinutes(now());

                $texto .= "\n⏱ Duración del almuerzo: {$minutos} minutos";

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Duración de la pausa
        |--------------------------------------------------------------------------
        */

        if ($tipo == 'pausa_fin') {

            $inicio = Marcaciones::where('empleado_id', $empleado->id)
                ->whereDate('fecha', today())
                ->where('tipo', 'pausa_inicio')
                ->latest('fecha_hora')
                ->first();

            if ($inicio) {

                $minutos = $inicio->fecha_hora->diffInMinutes(now());

                $texto .= "\n⏱ Duración de la pausa: {$minutos} minutos";

            }

        }

        $texto .= "\n\n";

        $texto .= $datos['mensaje'];

        return $texto;
    }

}