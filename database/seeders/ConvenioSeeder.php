<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Convenio;

class ConvenioSeeder extends Seeder
{
    public function run(): void
    {
        $convenios = [
    [
        'empresa_id'=> null,
        'codigo' => 'GEN001',
        'nombre' => 'GENERAL LEY DE CONTRATO DE TRABAJO',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,      // MODIFICADO: No existe banco de horas en LCT general
        'permite_compensatorio' => false,
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => false,
        'genera_compensatorio_feriado' => false,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: Sábado inglés según Art. 201 LCT
        'domingo_100' => true,
        'feriado_100' => true,        
        'considera_nocturnidad' => true,
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'COM130',
        'nombre' => 'EMPLEADOS DE COMERCIO',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => false,
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.       
        'genera_compensatorio_domingo' => false,
        'genera_compensatorio_feriado' => false,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',
        'domingo_100' => true,
        'feriado_100' => true,        
        'considera_nocturnidad' => true,      // MODIFICADO: Comercio sí se rige por la nocturnidad general
        'inicio_nocturnidad' => '21:00:00',   // MODIFICADO: Horario estándar LCT
        'fin_nocturnidad' => '06:00:00',      // MODIFICADO: Horario estándar LCT
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'BAN644',
        'nombre' => 'BANCARIOS',
        'jornada_diaria' => 7.5,
        'jornada_semanal' => 37.5,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => false, 
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.       
        'genera_compensatorio_domingo' => false,
        'genera_compensatorio_feriado' => false,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: Aunque no trabajan sábados habitualmente, si lo hacen es al 100%
        'domingo_100' => true,
        'feriado_100' => true,        
        'considera_nocturnidad' => false,     // Correcto, no es común la jornada nocturna bancaria estándar
        'inicio_nocturnidad' => null,
        'fin_nocturnidad' => null,
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'MUN001',
        'nombre' => 'MUNICIPALES',
        'jornada_diaria' => 7,
        'jornada_semanal' => 35,
        'permite_horas_extras' => true,
        'permite_banco_horas' => true,       // Correcto para sector público (Estatutos Municipales)
        'permite_compensatorio' => true, 
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.       
        'genera_compensatorio_domingo' => true,
        'genera_compensatorio_feriado' => true,
        'genera_compensatorio_sabado_100' => true,     // Correcto para sector público
        'sabado_desde_100' => '13:00:00',
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,      // MODIFICADO: Suelen tener adicionales por nocturnidad (ej: serenos, guardias)
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'UOM260',
        'nombre' => 'METALURGICOS',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => false,
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => false,
        'genera_compensatorio_feriado' => false,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: Sábado inglés obligatorio para metalúrgicos
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'UTE736',
        'nombre' => 'UTEDYC',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => true,     // Correcto, UTEDYC suele usar francos compensatorios por eventos los fines de semana
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => true,
        'genera_compensatorio_feriado' => true,
        'genera_compensatorio_sabado_100' => true,
        'sabado_desde_100' => '13:00:00',
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,      // MODIFICADO: Personal de clubes/predios con guardias nocturnas
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'SAN122',
        'nombre' => 'SANIDAD',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => true,     // MODIFICADO: Sanidad trabaja con esquemas de guardias y francos compensatorios
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => true,
        'genera_compensatorio_feriado' => true,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: Aplica el recargo si excede el ciclo legal
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'SEG507',
        'nombre' => 'SEGURIDAD PRIVADA',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,       // MODIFICADO: No rige banco de horas autónomo legalmente
        'permite_compensatorio' => true,     // Correcto, se otorgan francos compensatorios por esquemas 12x36 o rotativos
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => true,
        'genera_compensatorio_feriado' => true,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'CAM040',
        'nombre' => 'CAMIONEROS',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => false,
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => false,
        'genera_compensatorio_feriado' => false,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: El CCT 40/89 rige horas al 100% el sábado después de las 13hs
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'UOC76',
        'nombre' => 'CONSTRUCCION UOCRA',
        'jornada_diaria' => 8,
        'jornada_semanal' => 44,             // Correcto, jornada típica de la construcción de 44 horas
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => false,
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => false,
        'genera_compensatorio_feriado' => false,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: Sábado inglés estándar
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,      // MODIFICADO: UOCRA contempla serenos e infraestructura nocturna
        'inicio_nocturnidad' => '21:00:00',   // MODIFICADO: Seteo de horario nocturno
        'fin_nocturnidad' => '06:00:00',      // MODIFICADO: Seteo de horario nocturno
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'GAS389',
        'nombre' => 'GASTRONOMICOS',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => true,     // MODIFICADO: Gastronómicos tiene alta rotación de francos semanales
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => true,
        'genera_compensatorio_feriado' => true,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: Sábado inglés estándar (aplica según el franco del empleado)
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'PUB214',
        'nombre' => 'ADMINISTRACION PUBLICA NACIONAL',
        'jornada_diaria' => 7,
        'jornada_semanal' => 35,
        'permite_horas_extras' => true,
        'permite_banco_horas' => true,       // Correcto por Convenio Colectivo del Sector Público (Decreto 214/06)
        'permite_compensatorio' => true,     // Correcto para estatales
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => true,
        'genera_compensatorio_feriado' => true,
        'genera_compensatorio_sabado_100' => true,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: Sábado inglés estándar
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,      // MODIFICADO: Hay áreas del estado con turnos 24hs
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'SEG108',
        'nombre' => 'SEGUROS',
        'jornada_diaria' => 7,
        'jornada_semanal' => 35,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,
        'permite_compensatorio' => false,
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => false,
        'genera_compensatorio_feriado' => false,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // MODIFICADO: Sábado inglés estándar
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => false,
        'inicio_nocturnidad' => null,
        'fin_nocturnidad' => null,
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],

    [
        'empresa_id'=> null,
        'codigo' => 'PET547',
        'nombre' => 'PETROLEROS',
        'jornada_diaria' => 8,
        'jornada_semanal' => 48,
        'permite_horas_extras' => true,
        'permite_banco_horas' => false,       // MODIFICADO: No manejan banco de horas libre, se rigen estrictamente por diagramas legales (ej: 1x1, 2x1)
        'permite_compensatorio' => true,     // Correcto, el sistema de diagramas petroleros acumula francos compensatorios obligatorios
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => true,
        'genera_compensatorio_feriado' => true,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,      // MODIFICADO: En yacimientos e industrias petroleras la nocturnidad es clave
        'inicio_nocturnidad' => '21:00:00',   // MODIFICADO: Seteo de horario nocturno estándar
        'fin_nocturnidad' => '06:00:00',      // MODIFICADO: Seteo de horario nocturno estándar
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ],
    [
        'empresa_id'=> null, // Usualmente se especifica por empresa en terminales automotrices
        'codigo' => 'SMA027',
        'nombre' => 'MECANICOS SMATA',
        'jornada_diaria' => 8,
        'jornada_semanal' => 44,             // MODIFICADO: La jornada estándar de SMATA suele ser de 44 hs semanales (Monoturnos de Lunes a Viernes)
        'permite_horas_extras' => true,
        'permite_banco_horas' => true,       // MODIFICADO/EXCEPCIÓN: SMATA avala esquemas de acumulación y compensación de horas en terminales automotrices
        'permite_compensatorio' => true,     // Se complementa con el banco de horas
        'compensatorio_reemplaza_pago' => false, //cobra las horas extras (50% o 100%) y además genera el franco.
        'genera_compensatorio_domingo' => false,
        'genera_compensatorio_feriado' => false,
        'genera_compensatorio_sabado_100' => false,
        'sabado_desde_100' => '13:00:00',     // Sábado inglés estándar para horas fuera de convenio
        'domingo_100' => true,
        'feriado_100' => true,
        'considera_nocturnidad' => true,      // SMATA contempla trabajo nocturno y turnos rotativos (mucha actividad fabril es 24hs)
        'inicio_nocturnidad' => '21:00:00',
        'fin_nocturnidad' => '06:00:00',
        'vigente_desde' => '2025-01-01',
        'vigente_hasta' => null
    ]
];

        foreach ($convenios as $convenio) {

            Convenio::updateOrCreate(
                ['codigo' => $convenio['codigo']],
                $convenio
            );
        }
    }
}
