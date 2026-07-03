<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Empleado;
use App\Models\EmpleadoDispositivo;
use App\Models\EmpleadoSucursal;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;


class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $update = $request->all();

        if (!isset($update['message'])) {
            return response()->json(['ok' => true]);
        }

        $message = $update['message'];

        $chatId = $message['chat']['id'];

        $texto = trim($message['text'] ?? '');

        /*
        |--------------------------------------------------------------------------
        | /start ABC123
        |--------------------------------------------------------------------------
        */

        if (str_starts_with($texto, '/start')) {

            $partes = explode(' ', $texto);

            if (!isset($partes[1])) {

                $this->sendMessage(
                    $chatId,
                    "❌ El código QR no es válido."
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
                "👋 Bienvenido.\n\n🏢 Sucursal: {$sucursal->nombre}\n\n🆔 Ingrese su legajo."
            );

            return response()->json(['ok' => true]);
        }

        /*
        |--------------------------------------------------------------------------
        | Conversación en curso
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

                    "✅ Dispositivo registrado correctamente.\n\n" .
                    "Ya puede utilizar los siguientes comandos:\n\n" .
                    "🟢 /ingresar\n" .
                    "🔴 /salir"

                );

                return response()->json(['ok' => true]);
            }
        }

        $this->sendMessage(
            $chatId,
            "🤖 Comando no reconocido.\n\n" .
            "Comandos disponibles:\n\n" .
            "🟢 /ingresar\n" .
            "🔴 /salir"
            );

        return response()->json(['ok' => true]);
    }

    /**
     * Envía un mensaje a Telegram.
     */
    private function sendMessage(
        int|string $chatId,
        string $texto
    ): void {

        Http::post(

            'https://api.telegram.org/bot'
            . env('TELEGRAM_BOT_TOKEN')
            . '/sendMessage',

            [
                'chat_id' => $chatId,
                'text' => $texto
            ]

        );

    }
}