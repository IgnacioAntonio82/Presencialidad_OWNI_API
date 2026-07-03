<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Feriado;
use App\Models\Empleado;
use App\Models\Marcacion;
use App\Models\BancoHora;
use App\Models\JornadaLaboral;
use App\Models\EmpleadoHorario;
use Illuminate\Support\Facades\DB;
use App\Models\NovedadEmpleado;
use App\Models\EmpleadoConvenio;
use App\Models\FrancoCompensatorio;

class JornadaLaboralService
{
    public function procesar($empleadoId, $fecha)
    {
        return DB::transaction(function () use (
            $empleadoId,
            $fecha
        ) {

            $fechaCarbon = Carbon::parse($fecha);

            /*
            |--------------------------------------------------------------------------
            | Empleado
            |--------------------------------------------------------------------------
            */

            $empleado = Empleado::find($empleadoId);

            if (!$empleado) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | Obtencion de convenio vigente
            |--------------------------------------------------------------------------
            */

            $convenio = $empleado->convenioVigente($fecha);

            if (!$convenio) {
                throw new \Exception(
                    'El empleado no posee convenio vigente'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Configuración convenio
            |--------------------------------------------------------------------------
            */

            $configConvenio = [

                'jornada_diaria' =>
                    $convenio->jornada_diaria * 60,

                'permite_extras' =>
                    $convenio->permite_horas_extras,

                'permite_banco' =>
                    $convenio->permite_banco_horas,

                'permite_compensatorio' =>
                    $convenio->permite_compensatorio,

                'compensatorio_reemplaza_pago' =>
                    $convenio->compensatorio_reemplaza_pago,

                'genera_compensatorio_domingo' =>
                    $convenio->genera_compensatorio_domingo,

                'genera_compensatorio_feriado' =>
                    $convenio->genera_compensatorio_feriado,

                'genera_compensatorio_sabado_100' =>
                    $convenio->genera_compensatorio_sabado_100,

                'sabado_desde_100' =>
                    $convenio->sabado_desde_100,

                'domingo_100' =>
                    $convenio->domingo_100,

                'feriado_100' =>
                    $convenio->feriado_100,

                'nocturnidad' =>
                    $convenio->considera_nocturnidad,
                
                'inicio_nocturnidad' =>
                    $convenio->inicio_nocturnidad,

                'fin_nocturnidad' =>
                    $convenio->fin_nocturnidad,
            ];



            /*
            |--------------------------------------------------------------------------
            | Marcaciones del día
            |--------------------------------------------------------------------------
            */

            $marcaciones = Marcacion::where(
                'empleado_id',
                $empleadoId
            )
            ->whereDate(
                'fecha_hora',
                $fecha
            )
            ->orderBy('fecha_hora')
            ->get();

            // if ($marcaciones->isEmpty()) {
            //     return null;
            // }

            /*
            |--------------------------------------------------------------------------
            | Empresa
            |--------------------------------------------------------------------------
            */

            $empresaId = $empleado->empresa_id;

            /*
            |--------------------------------------------------------------------------
            | Novedades
            |--------------------------------------------------------------------------
            */

            $novedad = NovedadEmpleado::where(
                    'empleado_id',
                    $empleadoId
                )
                ->whereDate(
                    'fecha_desde',
                    '<=',
                    $fecha
                )
                ->whereDate(
                    'fecha_hasta',
                    '>=',
                    $fecha
                )
                ->where(
                    'estado',
                    'aprobado'
                )
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Feriado
            |--------------------------------------------------------------------------
            */
            $esFeriado = Feriado::whereDate(
                'fecha',
                $fecha
            )
            ->where('activo', true)
            ->where(function ($query) use ($empresaId) {

                $query->where('empresa_id', $empresaId)
                    ->orWhereNull('empresa_id');
            })
            ->exists();

            $esDomingo =
                $fechaCarbon->dayOfWeekIso === 7;

            /*
            |--------------------------------------------------------------------------
            | Tipo jornada
            |--------------------------------------------------------------------------
            */

            $tipoJornada = 'normal';

            if ($esFeriado) {

                $tipoJornada = 'feriado';

            } elseif ($esDomingo) {

                $tipoJornada = 'domingo';
            }

            
            /*
            |--------------------------------------------------------------------------
            | Horario vigente
            |--------------------------------------------------------------------------
            */

            $asignacionHorario = EmpleadoHorario::with([

                'horario.dias'

            ])

            ->where(
                'empleado_id',
                $empleadoId
            )

            ->whereDate(
                'vigente_desde',
                '<=',
                $fecha
            )

            ->where(function ($query) use ($fecha) {

                $query->whereNull('vigente_hasta')

                    ->orWhereDate(
                        'vigente_hasta',
                        '>=',
                        $fecha
                    );
            })

            ->first();

            $horario = null;

            if ($asignacionHorario) {

                $horarioCandidato =
                    $asignacionHorario->horario;

                /*
                |--------------------------------------------------------------------------
                | Verificar día aplicable
                |--------------------------------------------------------------------------
                */

                $aplicaDia =
                    $horarioCandidato
                        ->dias
                        ->contains(

                            'dia_semana',

                            $fechaCarbon->dayOfWeekIso
                        );

                if ($aplicaDia) {

                    $horario = $horarioCandidato;
                }
            }

            /*
                |--------------------------------------------------------------------------
                | Novedad aprobada
                |--------------------------------------------------------------------------
                */

                if ($novedad) {

                    return JornadaLaboral::updateOrCreate(

                        [
                            'empleado_id' => $empleadoId,
                            'fecha' => $fecha,
                        ],

                        [
                            'empresa_id' => $empresaId,

                            'horario_id' => $horario?->id,

                            'horario_ingreso' => $horario?->hora_ingreso,

                            'horario_salida' => $horario?->hora_salida,

                            'ingreso_real' => null,

                            'salida_real' => null,

                            'minutos_normales' => 0,

                            'minutos_extra_50' => 0,

                            'minutos_extra_100' => 0,

                            'minutos_banco_horas' => 0,

                            'minutos_almuerzo' => 0,

                            'minutos_pausa' => 0,

                            'minutos_tardanza' => 0,

                            'minutos_trabajados' => 0,

                            'minutos_nocturnos' => 0,
                                

                            'es_feriado' => false,

                            'es_domingo' => false,

                            'estado' => 'procesado',

                            'tipo_jornada' => $novedad->tipo,
                            
                            'es_novedad' => true,
                        ]
                    );
                }



            /*
            |--------------------------------------------------------------------------
            | Sin marcaciones
            |--------------------------------------------------------------------------
            */

            if ($marcaciones->isEmpty()) {

                if (
                    $esFeriado &&
                    $configConvenio['feriado_100']
                ) {

                    return JornadaLaboral::updateOrCreate(

                        [
                            'empleado_id' => $empleadoId,
                            'fecha' => $fecha,
                        ],

                        [
                            'empresa_id' => $empresaId,

                            'horario_id' => $horario?->id,

                            'horario_ingreso' => $horario?->hora_ingreso,

                            'horario_salida' => $horario?->hora_salida,

                            'ingreso_real' => null,

                            'salida_real' => null,

                            'minutos_normales' => 0,

                            'minutos_extra_50' => 0,

                            'minutos_extra_100' => 0,

                            'minutos_banco_horas' => 0,

                            'minutos_almuerzo' => 0,

                            'minutos_pausa' => 0,

                            'minutos_tardanza' => 0,

                            'minutos_trabajados' => 0,

                            'minutos_nocturnos' => 0,
                                        

                            'es_feriado' => true,

                            'es_domingo' => $esDomingo,

                            'estado' => 'procesado',

                            'tipo_jornada' => $tipoJornada,
                            
                        ]
                    );
                }

                return null;
            }






            /*
            |--------------------------------------------------------------------------
            | Horario vigente
            |--------------------------------------------------------------------------
            */

            // $asignacionHorario = EmpleadoHorario::with([

            //     'horario.dias'

            // ])

            // ->where(
            //     'empleado_id',
            //     $empleadoId
            // )

            // ->whereDate(
            //     'vigente_desde',
            //     '<=',
            //     $fecha
            // )

            // ->where(function ($query) use ($fecha) {

            //     $query->whereNull('vigente_hasta')

            //         ->orWhereDate(
            //             'vigente_hasta',
            //             '>=',
            //             $fecha
            //         );
            // })

            // ->first();

            // $horario = null;

            // if ($asignacionHorario) {

            //     $horarioCandidato =
            //         $asignacionHorario->horario;

            //     /*
            //     |--------------------------------------------------------------------------
            //     | Verificar día aplicable
            //     |--------------------------------------------------------------------------
            //     */

            //     $aplicaDia =
            //         $horarioCandidato
            //             ->dias
            //             ->contains(

            //                 'dia_semana',

            //                 $fechaCarbon->dayOfWeekIso
            //             );

            //     if ($aplicaDia) {

            //         $horario = $horarioCandidato;
            //     }
            // }

            /*
            |--------------------------------------------------------------------------
            | Ingreso / salida
            |--------------------------------------------------------------------------
            */

            $ingreso = $marcaciones
                ->where('tipo', 'ingreso')
                ->first();

            $salida = $marcaciones
                ->where('tipo', 'salida')
                ->last();
                       

            // if (!$ingreso || !$salida) {
            //     return null;
            // }

            //$ingresoReal = Carbon::parse(
             //   $ingreso->fecha_hora
           // );

           // $salidaReal = Carbon::parse(
            //    $salida->fecha_hora
           // );

            /*
            |--------------------------------------------------------------------------
            | Sin ingreso no se puede procesar
            |--------------------------------------------------------------------------
            */
            $salidaEstimada = false;

            if (!$ingreso) {
                return null;
            }

            $ingresoReal = Carbon::parse(
                $ingreso->fecha_hora
            );

            $salidaEstimada = false;

            /*
            |--------------------------------------------------------------------------
            | Salida registrada
            |--------------------------------------------------------------------------
            */

            if ($salida) {

                $salidaReal = Carbon::parse(
                    $salida->fecha_hora
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | Sin salida registrada
                |--------------------------------------------------------------------------
                */

                if (!$horario) {

                    /*
                    |--------------------------------------------------------------------------
                    | Sin horario no se puede estimar
                    |--------------------------------------------------------------------------
                    */

                    return null;
                }

                $horaIngresoProgramada = Carbon::parse(
                    $fecha . ' ' . $horario->hora_ingreso
                );

                $horaSalidaProgramada = Carbon::parse(
                    $fecha . ' ' . $horario->hora_salida
                );

                /*
                |--------------------------------------------------------------------------
                | Turno nocturno
                |--------------------------------------------------------------------------
                */

                if (
                    $horaSalidaProgramada->lessThanOrEqualTo(
                        $horaIngresoProgramada
                    )
                ) {
                    $horaSalidaProgramada->addDay();
                }

                /*
                |--------------------------------------------------------------------------
                | Todavía no terminó el turno
                |--------------------------------------------------------------------------
                */

                if (now()->lessThan($horaSalidaProgramada)) {
                    return null;
                }

                /*
                |--------------------------------------------------------------------------
                | Tomar salida programada
                |--------------------------------------------------------------------------
                */

                $salidaReal = $horaSalidaProgramada;

                $salidaEstimada = true;
            }

            


            /*
            |--------------------------------------------------------------------------
            | Empresa
            |--------------------------------------------------------------------------
            */

            //$empresaId = $empleado->empresa_id;

            /*
            |--------------------------------------------------------------------------
            | Minutos trabajados
            |--------------------------------------------------------------------------
            */

            $minutosTrabajados = $ingresoReal
                ->diffInMinutes($salidaReal);


            /*
            |--------------------------------------------------------------------------
            | Nocturnidad
            |--------------------------------------------------------------------------
            */
            $minutosNocturnos = 0;

            if (
                $configConvenio['nocturnidad']
            ) {

                $inicioNocturno = Carbon::parse(
                    $fecha .
                    ' ' .
                    $configConvenio['inicio_nocturnidad']       
                );

                $finNocturno = Carbon::parse(
                    $fecha .
                    ' ' .
                    $configConvenio['fin_nocturnidad']
                );

                if (
                    $finNocturno
                        ->lessThanOrEqualTo(
                            $inicioNocturno
                        )
                ) {

                    $finNocturno->addDay();
                }

                $inicioTrabajo =
                    $ingresoReal->copy();

                $finTrabajo =
                    $salidaReal->copy();

                $inicio =
                    $inicioTrabajo->max(
                        $inicioNocturno
                    );

                $fin =
                    $finTrabajo->min(
                        $finNocturno
                    );

                if ($inicio < $fin) {

                    $minutosNocturnos =
                        $inicio->diffInMinutes(
                            $fin
                        );
                }
            }
            

            /*
            |--------------------------------------------------------------------------
            | Almuerzo
            |--------------------------------------------------------------------------
            */

            $almuerzoInicio = $marcaciones
                ->where('tipo', 'almuerzo_inicio')
                ->first();

            $almuerzoFin = $marcaciones
                ->where('tipo', 'almuerzo_fin')
                ->first();

            $minutosAlmuerzo = 0;

            if ($almuerzoInicio && $almuerzoFin) {

                $minutosAlmuerzo = Carbon::parse(
                    $almuerzoInicio->fecha_hora
                )->diffInMinutes(

                    Carbon::parse(
                        $almuerzoFin->fecha_hora
                    )
                );

                $minutosTrabajados -=
                    $minutosAlmuerzo;
            }

            /*
            |--------------------------------------------------------------------------
            | Feriado
            |--------------------------------------------------------------------------
            */

            // $esFeriado = Feriado::whereDate(
            //     'fecha',
            //     $fecha
            // )
            // ->where('activo', true)
            // ->where(function ($query) use ($empresaId) {

            //     $query->where('empresa_id', $empresaId)
            //         ->orWhereNull('empresa_id');
            // })
            // ->exists();

            // $esDomingo =
            //     $fechaCarbon->dayOfWeekIso === 7;

            /*
            |--------------------------------------------------------------------------
            | Jornada esperada
            |--------------------------------------------------------------------------
            */

            //$minutosJornada = 480;

            //$minutosJornada = $convenio->jornada_diaria * 60;

            $minutosJornada =  $configConvenio['jornada_diaria'];

            if ($horario) {

                $horaIngreso = Carbon::parse(
                    $fecha .
                    ' ' .
                    $horario->hora_ingreso
                );

                $horaSalida = Carbon::parse(
                    $fecha .
                    ' ' .
                    $horario->hora_salida
                );

                /*
                |--------------------------------------------------------------------------
                | Turno nocturno
                |--------------------------------------------------------------------------
                */

                if (
                    $horaSalida->lessThanOrEqualTo(
                        $horaIngreso
                    )
                ) {

                    $horaSalida->addDay();
                }

                $minutosJornada = $horaIngreso
                    ->diffInMinutes($horaSalida);

                $minutosJornada -=
                    $horario->minutos_almuerzo;
            }

            /*
            |--------------------------------------------------------------------------
            | Tardanza
            |--------------------------------------------------------------------------
            */

            // $minutosTardanza = 0;

            // if ($horario && !$esFeriado) {

            //     $horaIngresoEsperada =
            //         Carbon::parse(
            //             $fecha .
            //             ' ' .
            //             $horario->hora_ingreso
            //         );

            //     $minutosTardanza =
            //         max(

            //             $horaIngresoEsperada
            //                 ->diffInMinutes(
            //                     $ingresoReal,
            //                     false
            //                 ),

            //             0
            //         );

            //     /*
            //     |--------------------------------------------------------------------------
            //     | Aplicar tolerancia
            //     |--------------------------------------------------------------------------
            //     */

            //     if (

            //         $minutosTardanza <=
            //         $horario->tolerancia_tardanza

            //     ) {

            //         $minutosTardanza = 0;
            //     }
            // }

            $minutosTardanza = 0;

            if ($horario && !$esFeriado) {

                $horaIngresoEsperada =
                    Carbon::parse(
                        $fecha .
                        ' ' .
                        $horario->hora_ingreso
                    );

                /*
                |--------------------------------------------------------------------------
                | Tardanza real
                |--------------------------------------------------------------------------
                */

                $tardanzaReal = max(

                    $horaIngresoEsperada
                        ->diffInMinutes(
                            $ingresoReal,
                            false
                        ),

                    0
                );

                /*
                |--------------------------------------------------------------------------
                | Descontar tolerancia
                |--------------------------------------------------------------------------
                */

                $minutosTardanza = max(

                    $tardanzaReal -
                    $horario->tolerancia_tardanza,

                    0
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Cálculo extras
            |--------------------------------------------------------------------------
            */

            $minutosNormales = 0;

            $minutosExtra50 = 0;

            $minutosExtra100 = 0;

            $esSabado =
                $fechaCarbon->dayOfWeekIso === 6;


            $esDia100 = (

                $esFeriado &&
                $configConvenio['feriado_100']

            )

            ||

            (

                $esDomingo &&
                $configConvenio['domingo_100']

            );

            /*
            |--------------------------------------------------------------------------
            | Domingo / feriado
            |--------------------------------------------------------------------------
            */
            

            //if ($esFeriado || $esDomingo) 

            if ($esDia100)
           
            {

            

            //todo lo trabajado en feriado sea al 100%
            $minutosNormales = 0;
            $minutosExtra50 = 0;
            $minutosExtra100 = $minutosTrabajados;

        }

            /*
            |--------------------------------------------------------------------------
            | Sábado después de las 13 hs
            |--------------------------------------------------------------------------
            */

            elseif ($esSabado) {

                $limiteSabado = Carbon::parse(
                    $fecha .
                    ' ' .
                    $configConvenio['sabado_desde_100']
                );

                /*
                |--------------------------------------------------------------------------
                | Todo normal
                |--------------------------------------------------------------------------
                */

                if (
                    $salidaReal->lessThanOrEqualTo(
                        $limiteSabado
                    )
                ) {

                    $minutosNormales =
                        $ingresoReal->diffInMinutes(
                            $salidaReal
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | Parte normal + parte 100%
                |--------------------------------------------------------------------------
                */

                else {

                    /*
                    |--------------------------------------------------------------------------
                    | Minutos normales
                    |--------------------------------------------------------------------------
                    */

                    $finNormal =
                        $limiteSabado;

                    /*
                    |--------------------------------------------------------------------------
                    | Si ingresó después de las 13
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $ingresoReal->greaterThan(
                            $limiteSabado
                        )
                    ) {

                        $minutosNormales = 0;

                    } else {

                        $minutosNormales =
                            $ingresoReal
                                ->diffInMinutes(
                                    $finNormal
                                );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Minutos al 100%
                    |--------------------------------------------------------------------------
                    */

                    $inicio100 =
                        $ingresoReal->greaterThan(
                            $limiteSabado
                        )

                        ? $ingresoReal

                        : $limiteSabado;

                    $minutosExtra100 =
                        $inicio100->diffInMinutes(
                            $salidaReal
                        );
                }
            }

            

            /*
            |--------------------------------------------------------------------------
            | Días normales
            |--------------------------------------------------------------------------
            */

            else {

                $minutosNormales = min(

                    $minutosTrabajados,

                    $minutosJornada
                );

                

                $extras = max(

                    $minutosTrabajados -
                    $minutosJornada,

                    0
                );
               

                if (
                    $configConvenio['permite_extras']   
                    &&
                    $empleado->permite_horas_extras
                ) {

                    $minutosExtra50 = $extras;
                }

            }






            /*
            |--------------------------------------------------------------------------
            | Salida estimada
            |--------------------------------------------------------------------------
            */

            if ($salidaEstimada) {

                $minutosExtra50 = 0;

                $minutosExtra100 = 0;

                $minutosBancoHoras = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | Generar compensatorio
            |--------------------------------------------------------------------------
            */

            $trabajoSabado100 =

                $esSabado

                &&

                $minutosExtra100 > 0;

            $generaCompensatorio =

                (
                    $esDomingo &&
                    $configConvenio['genera_compensatorio_domingo']
                )

                ||

                (
                    $esFeriado &&
                    $configConvenio['genera_compensatorio_feriado']
                )

                ||

                (
                    $trabajoSabado100 &&
                    $configConvenio['genera_compensatorio_sabado_100']
                );







            /*
            |--------------------------------------------------------------------------
            | Banco de horas / extras / compensatorio
            |--------------------------------------------------------------------------
            */

            $minutosBancoHoras = 0;

            /*
            |--------------------------------------------------------------------------
            | Modo compensatorio
            |--------------------------------------------------------------------------
            */

            if (

                $empleado->modo_horas_extras === 'compensatorio'

                &&

                $configConvenio['permite_compensatorio']

                &&
                $generaCompensatorio

            ) {

                /*
                |--------------------------------------------------------------------------
                | No generar extras ni banco
                |--------------------------------------------------------------------------
                */

                 if ($configConvenio['compensatorio_reemplaza_pago']) {

                    $minutosExtra50 = 0;
                    $minutosExtra100 = 0;
                }

                //$minutosExtra50 = 0;

                //$minutosExtra100 = 0;

                $minutosBancoHoras = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | Extras / Banco horas
            |--------------------------------------------------------------------------
            */

            
            elseif ( $empleado->permite_horas_extras && ( $configConvenio['permite_extras']  || $configConvenio['permite_banco'] ))
            {

                /*
                |--------------------------------------------------------------------------
                | Total extras
                |--------------------------------------------------------------------------
                */

                $totalExtras =
                    $minutosExtra50 +
                    $minutosExtra100;

                /*
                |--------------------------------------------------------------------------
                | Límite diario
                |--------------------------------------------------------------------------
                */

                if (
                    $empleado->max_minutos_extras_dia
                ) {

                    $totalExtras = min(

                        $totalExtras,

                        $empleado->max_minutos_extras_dia
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Modo banco horas
                |--------------------------------------------------------------------------
                */

                if (
                    $empleado->modo_horas_extras ==='banco_horas'  && $configConvenio['permite_banco']) { 

                    $minutosBancoHoras =
                        $totalExtras;

                    $minutosExtra50 = 0;

                    $minutosExtra100 = 0;
                }

                /*
                |--------------------------------------------------------------------------
                | Modo mixto
                |--------------------------------------------------------------------------
                */

                elseif (
                    $empleado->modo_horas_extras ===  'mixto' &&  $configConvenio['permite_banco']
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Extras originales
                    |--------------------------------------------------------------------------
                    */

                    $extrasOriginales =
                        $minutosExtra50 +
                        $minutosExtra100;

                    /*
                    |--------------------------------------------------------------------------
                    | Banco horas 50%
                    |--------------------------------------------------------------------------
                    */

                    $minutosBancoHoras =
                        intval(
                            $totalExtras * 0.5
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Extras restantes
                    |--------------------------------------------------------------------------
                    */

                    $extrasRestantes =
                        $totalExtras -
                        $minutosBancoHoras;

                    /*
                    |--------------------------------------------------------------------------
                    | Mantener proporción 50 / 100
                    |--------------------------------------------------------------------------
                    */

                    if ($extrasOriginales > 0) {

                        $factor =
                            $extrasRestantes /
                            $extrasOriginales;

                        $minutosExtra50 =
                            intval(
                                $minutosExtra50 * $factor
                            );

                        $minutosExtra100 =
                            intval(
                                $minutosExtra100 * $factor
                            );
                    }
                }
                /*
                |--------------------------------------------------------------------------
                | Modo pagar
                |--------------------------------------------------------------------------
                */

                else {

                   /*
                    |--------------------------------------------------------------------------
                    | Mantener extras originales
                    |--------------------------------------------------------------------------
                    */

                    $minutosExtra50 = min(
                        $minutosExtra50,
                        $totalExtras
                    );

                    $minutosExtra100 = min(
                        $minutosExtra100,
                        $totalExtras
                    );

                    $minutosBancoHoras = 0;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Guardar jornada
            |--------------------------------------------------------------------------
            */

            $jornada =
                JornadaLaboral::updateOrCreate(

                [
                    'empleado_id' =>
                        $empleadoId,

                    'fecha' =>
                        $fecha
                ],

                [
                    'empresa_id' =>
                        $empresaId,

                    'horario_id' =>
                        $horario?->id,

                    'horario_ingreso' =>
                        $horario?->hora_ingreso,

                    'horario_salida' =>
                        $horario?->hora_salida,

                    'ingreso_real' =>
                        $ingresoReal,

                    'salida_real' =>
                        $salidaReal,

                    'minutos_normales' =>
                        $minutosNormales,

                    'minutos_extra_50' =>
                        $minutosExtra50,

                    'minutos_extra_100' =>
                        $minutosExtra100,

                    'minutos_banco_horas' =>
                        $minutosBancoHoras,

                    'minutos_almuerzo' =>
                        $minutosAlmuerzo,

                    'minutos_pausa' =>
                        0,

                    'minutos_tardanza' =>
                        $minutosTardanza,

                    'minutos_trabajados' =>
                        $minutosTrabajados,
                    
                    'minutos_nocturnos' =>
                        $minutosNocturnos,

                    'es_feriado' =>
                        $esFeriado,

                    'es_domingo' =>
                        $esDomingo,

                    'estado' =>
                        $salidaEstimada
                        ? 'incompleta'
                        : 'procesado',
                        
                    'tipo_jornada' =>
                        $tipoJornada,   
                        
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Franco compensatorio
            |--------------------------------------------------------------------------
            */

            if (

                $configConvenio['permite_compensatorio']

                &&

                $empleado->modo_horas_extras === 'compensatorio'

                &&

                $generaCompensatorio

                &&

                $minutosTrabajados >= 240

                &&
                !$salidaEstimada

            ) {

                if ($esSabado) {
                    $minutosGenerados = $jornada->minutos_extra_100;
                } else {
                    $minutosGenerados = $jornada->minutos_trabajados;
                }


                FrancoCompensatorio::firstOrCreate(

                    [
                        'jornada_laboral_id' => $jornada->id
                    ],

                    [
                        'empresa_id' => $empresaId,
                        'empleado_id' => $empleadoId,
                        'fecha_generacion' => $fecha,
                        'minutos_generados' => $minutosGenerados,
                        'cantidad_dias' => 1,
                        'motivo' =>
                            $esDomingo
                                ? 'Trabajo en domingo'

                            : (

                                $esFeriado
                                    ? 'Trabajo en feriado'

                                    : 'Trabajo en sábado después de las 13 hs'
                            ),
                        'estado' => 'pendiente'
                    ]
                );
            }else {
                FrancoCompensatorio::where(
                    'jornada_laboral_id',
                    $jornada->id
                )
                ->where(
                    'estado',
                    'pendiente'
                )
                ->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | Limpiar movimientos previos
            |--------------------------------------------------------------------------
            */

            BancoHora::where(
                'jornada_laboral_id',
                $jornada->id
            )->delete();

            /*
            |--------------------------------------------------------------------------
            | Crear movimiento banco horas
            |--------------------------------------------------------------------------
            */

            if ($minutosBancoHoras > 0) {

                BancoHora::create([

                    'empresa_id' =>
                        $empresaId,

                    'empleado_id' =>
                        $empleadoId,

                    'jornada_laboral_id' =>
                        $jornada->id,

                    'tipo' =>
                        'credito',

                    'minutos' =>
                        $minutosBancoHoras,

                    'fecha' =>
                        $fecha,

                    'motivo' =>
                        'Horas extras'
                ]);
            }

            return $jornada->load([

                'empresa',

                'empleado',

                'horario',

                'bancoHoras'
            ]);
        });
    }
}