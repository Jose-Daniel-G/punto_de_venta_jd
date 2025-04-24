<?php

namespace App\Imports;

use App\Models\NotificacionAviso;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Shared\Date;

use Maatwebsite\Excel\Concerns\Importable;

class NotificacionAvisoImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable;
    public $errores = [];

    private $publi_notificacion;
    private $tipoPlantillaId;
    private $organismoId;
    private $rutaArchivoExel;
    private $estadoAuditoriaId;
    private $fk_idusuario;
    private $username;
    private $extension;

    public function setConfiguracion($publi_notificacion, $tipoPlantillaId, $organismoId, $rutaArchivoExel, $estadoAuditoriaId, $fk_idusuario, $username, $extension)
    {
        $this->publi_notificacion = $publi_notificacion;
        $this->tipoPlantillaId = $tipoPlantillaId;
        $this->organismoId = $organismoId;
        $this->rutaArchivoExel = $rutaArchivoExel;
        $this->estadoAuditoriaId = $estadoAuditoriaId;
        $this->fk_idusuario = $fk_idusuario;
        $this->username = $username;
        $this->extension = $extension;
    }

    public function model(array $row)
    {
        try {
            if ($this->extension === 'csv') {
                // Validar campos obligatorios
                if (!isset($row['nombre_ciudadano']) || empty(trim($row['nombre_ciudadano']))) {
                    $this->errores[] = "El campo 'nombre_ciudadano' es obligatorio.";
                    return null;
                }
                if (!isset($row['cedula_identificacion']) || !ctype_digit(strval($row['cedula_identificacion']))) {
                    $this->errores[] = "El campo 'cedula_identificacion' debe contener solo dígitos.";
                    return null;
                }
            }
            // if (!$this->validarCaracteresEspeciales($row)) {
            //     return null;
            // }

            // if (!$this->validarFechas($row)) {
            //     return null;
            // }
            if (in_array($this->tipoPlantillaId, [1, 2])) {
                if ($this->extension === 'csv') {
                    $variable = $this->validarCampoNumerico($row, 'tipo_impuesto', 'Tipo de impuesto');
                    if (!$variable) {
                        return null; //fk_idtp_imp
                    }

                    if (!$this->validarCampoNumerico($row, 'tipo_causa_devolucion', 'Tipo de causa de devolución')) {
                        return null; //fk_tipo_causa_devolucion
                    }

                    if (!$this->validarCampoNumerico($row, 'tipo_acto_tramite', 'Tipo de acto de trámite')) {
                        return null; //fk_tipo_acto_tramite
                    }

                    if (!$this->validarCampoNumerico($row, 'tipo_estado_publicacion', 'Tipo de estado de publicación')) {
                        return null; //fk_tipo_estado_publicacion
                    }
                }
                // dd(['row'=>$row]);
                $this->coactivo_persuasivo($row);
            } else {
                // dd(['row'=>$row]);
                if (!$this->validarLiquidacion($row, 'liquidacion')) {
                    $this->errores[] = "El campo LIQUIDACIÓN debe contener solo números. Valor recibido: " . $liquidacion;
                    return null; //fk_tipo_causa_devolucion
                }
                $this->liquidaciones($row);
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error al procesar la fila: " . json_encode($row) . " - " . $e->getMessage());
            return null; // En caso de error, omite la fila
        }
    }

    public function coactivo_persuasivo($row)
    {
        $fechas = $this->conversionDateExcelMonth($row['fecha_publicacion'], $row['fecha_desfijacion'], 1);
        // dd($fechas);
        if (!$fechas) {
            return; // o sigue al siguiente registro, dependiendo de tu flujo
        }
        try {
            NotificacionAviso::create([
                'publi_notificacion'       => $this->publi_notificacion,
                'fk_idorganismo'          => $this->organismoId,
                'fk_idusuario'            => $this->fk_idusuario ?? Auth::id(), // asegúrate de que nunca sea null
                'fk_idtp_imp'             =>  $row['tipo_impuesto'],
                'fk_tipo_plantilla'       => $this->tipoPlantillaId,
                'ruta_archivos'           => $this->rutaArchivoExel,
                'nombre_ciudadano'        => $row['nombre_ciudadano'],
                'cedula_identificacion'   => $row['cedula_identificacion'],
                'fecha_publicacion'       => $fechas['fecha_publicacion']->format('Y-m-d'),
                'fecha_desfijacion'       => $fechas['fecha_desfijacion']->format('Y-m-d'),
                'id_predio'               => null,
                'objeto_contrato'         => null,
                'num_predial'             => null,
                'fk_publi_noti'           => null,
                'fk_tipo_acto_tramite'    => $row['tipo_acto_tramite'] ?? null,
                'fk_estado_publicacion'   => $row['estado_publicacion'] ?? null,
                'fk_tipo_causa_devolucion' => $row['tipo_causa_devolucion'] ?? null,
                'json_plantilla'          => json_encode([
                    'data' => $row[7] ?? []
                ]),
                'id_estado_auditoria'     => $this->estadoAuditoriaId,
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function liquidaciones($row)
    {
        
        $fechas = $this->conversionDateExcelDay($row['fecha_publicacion'], $row['fecha_desfijacion'], 5); // 5 dias
        //         dd($fechas,$row);
        // dd( $fechas['fecha_publicacion']->format('Y-m-d'));
        NotificacionAviso::create([
            'publi_notificacion'       => $this->publi_notificacion,
            'fk_idorganismo'          => $this->organismoId,
            'fk_idusuario'            => Auth::id(),
            'fk_idtp_imp'             => '1',
            'fk_tipo_plantilla'       => $this->tipoPlantillaId,
            'ruta_archivos'           => $this->rutaArchivoExel,
            'nombre_ciudadano'        => $row['nombre_ciudadano'],
            'cedula_identificacion'   => $row['cedula_identificacion'],
            'fecha_publicacion'       => $fechas['fecha_publicacion']->format('Y-m-d'),
            'fecha_desfijacion'       => $fechas['fecha_publicacion']->format('Y-m-d'),
            'id_predio'               => $row['id_predio'] ?? null,
            'objeto_contrato'         => $row['objeto_contrato'],
            'num_predial'             => $row['num_predial'],
            'fk_publi_noti'           => $row['publi_noti'] ?? null,
            'fk_tipo_acto_tramite'    => $row['tipo_acto_tramite'] ?? null,
            'fk_estado_publicacion'   => $row['estado_publicacion'] ?? null,
            'fk_tipo_causa_devolucion' => $row['tipo_causa_devolucion'] ?? null,
            'json_plantilla'          => json_encode([
                'data' => $row[7] ?? []
            ]),
            'id_estado_auditoria'     => $this->estadoAuditoriaId,
        ]);
    }
    private function validarCaracteresEspeciales($row)
    {
        foreach ($row as $campo => $valor) {

            if ($campo === 'id_predio') {
                if (!preg_match('/^[0-9]+$/', $valor)) {return false;   } // Solo números permitidos
            } else {
                // if (!preg_match('/^[0-9\/\-]+$/', $valor)) {# Para otros campos permites números, / y -
                if (!preg_match('/^[a-zA-Z0-9\/\-\s]+$/', $valor)) {return false;} #// Letras, números, espacio,
            }
        }
        return true;
    }
    public function validarLiquidacion($row, $campo)
    {
        $liquidacion = $row[$campo];

        if ($liquidacion === '') {
            $this->errores[] = "El campo LIQUIDACION es obligatorio.";
            return false;
        }

        if (is_object($liquidacion) && method_exists($liquidacion, '__toString')) {
            $liquidacion = $liquidacion->__toString();
        }

        $liquidacion = trim($liquidacion);

        if (!ctype_digit($liquidacion)) {
            return false;
        }
        return true;
    }

    public function validarFechas($row)
    {
        foreach (['fecha_publicacion', 'fecha_desfijacion'] as $campoFecha) {

            if (!isset($row[$campoFecha]) || empty($row[$campoFecha])) {
                $this->errores[] = "Fila " . ($row['#'] ?? 'desconocida') . ": El campo '{$campoFecha}' es obligatorio.";
                return false; // Devuelve false si hay un error
            }
            // Intentar primero con el formato que Excel exporta: M/D/YYYY
            $fecha = DateTime::createFromFormat('n/j/Y', $row[$campoFecha]);

            // Si falla, intentar con el formato correcto YYYY-MM-DD
            if (!$fecha) {
                $fecha = DateTime::createFromFormat('Y-m-d', $row[$campoFecha]);
            }

            // Si todavía es inválida
            if (!$fecha) {
                $this->errores[] = "Fila " . ($row['#'] ?? 'desconocida') . ": El campo '{$campoFecha}' debe estar en formato YYYY-MM-DD o MM/DD/YYYY.";
                return false;
            }

            // Convertirla al formato correcto para guardar
            $row[$campoFecha] = $fecha->format('Y-m-d');
        }

        return true; // Devuelve true si las fechas son válidas
    }
    //VALIDACIONES COBRO COACTIVO Y PERSUASIVO
    public function validarCampoNumerico($row, $campo, $nombreCampo)
    {
        if (!isset($row[$campo]) || empty($row[$campo])) {
            $this->errores[] = "Fila " . ($row['#'] ?? 'desconocida') . ": El campo '{$nombreCampo}' es obligatorio.";
            return false;
        }

        if (!is_numeric($row[$campo])) {
            $this->errores[] = "Fila " . ($row['#'] ?? 'desconocida') . ": El campo '{$nombreCampo}' debe contener un número.";
            return false;
        }

        return true;
    }

    private function parseFechaExcel($fecha)
    {
        // Si el valor es un número, lo convertimos desde Excel
        if (is_numeric($fecha)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha)->format('Y-m-d');
        }

        // Si ya es una fecha en texto, intenta convertirlo.
        try {
            return \Carbon\Carbon::parse($fecha)->format('Y-m-d');
        } catch (\Exception $e) {
            return null; // o manejar el error como necesites
        }
    }

    // public function conversionDateExcelDay($fecha_publicacion, $fecha_desfijacion, $day)
    public function conversionDateExcelDay($fecha_publicacion, $fecha_desfijacion, $day)
    {
        if ($this->extension == 'csv') {
            if (!empty($fecha_publicacion) && !empty($fecha_desfijacion)) {
                $fecha_publicacion = Carbon::parse($fecha_publicacion);
                $fecha_desfijacion = Carbon::parse($fecha_desfijacion);
                if (!$fecha_desfijacion->equalTo($fecha_publicacion->copy()->addDays(5))) {
                    $this->errores[] = "⚠️ Error La fecha de desfijación debe ser 5 días después de la publicación.";
                    return null;
                }
            }


            return ['fecha_publicacion' => $fecha_publicacion, 'fecha_desfijacion' => $fecha_desfijacion];
        } else {
            if (!empty($fecha_publicacion) && !empty($fecha_desfijacion)) {
                try {
                    // dd($fecha_publicacion, $fecha_desfijacion);
                    $fecha_publicacion = Carbon::instance(Date::excelToDateTimeObject($fecha_publicacion));
                    $fecha_desfijacion = Carbon::instance(Date::excelToDateTimeObject($fecha_desfijacion));

                    if (!$fecha_desfijacion->equalTo($fecha_publicacion->copy()->addDays($day))) {
                        $this->errores[] = "⚠️ Error La fecha de desfijación debe ser $day días después de la publicación.";
                        return null;
                    }
                    return ['fecha_publicacion' =>  Carbon::instance($fecha_publicacion), 'fecha_desfijacion' =>  Carbon::instance($fecha_desfijacion)];
                } catch (\Exception $e) {
                    $this->errores[] = "⚠️ Error al convertir fechas desde Excel: " . $e->getMessage();
                    return null;
                }
            }
        }
    }
    public function conversionDateExcelMonth($fecha_publicacion, $fecha_desfijacion, $month)
    {
        if ($this->extension == 'csv') {
            if (!empty($fecha_publicacion) && !empty($fecha_desfijacion)) {
                $fecha_publicacion = Carbon::parse($fecha_publicacion);
                $fecha_desfijacion = Carbon::parse($fecha_desfijacion);
                if (!$fecha_desfijacion->equalTo($fecha_publicacion->copy()->addMonth($month))) {
                    $this->errores[] = "⚠️ Error La fecha de desfijación debe ser de 1 mes después de la publicación.";
                    return null;
                }
                return ['fecha_publicacion' => $fecha_publicacion, 'fecha_desfijacion' => $fecha_desfijacion];
            }
        } else {
            if (!empty($fecha_publicacion) && !empty($fecha_desfijacion)) {
                try {
                    $fecha_publicacion = Carbon::instance(Date::excelToDateTimeObject($fecha_publicacion));
                    $fecha_desfijacion = Carbon::instance(Date::excelToDateTimeObject($fecha_desfijacion));
                    // Crear la fecha de desfijación esperada agregando un mes
                    $fecha_desfijacion_esperada = $fecha_publicacion->copy()->addMonthNoOverflow();
                    // if (!$fecha_desfijacion->equalTo($fecha_publicacion->copy()->addMonth($month))) {
                        if (!$fecha_desfijacion->isSameDay($fecha_desfijacion_esperada)) {
                        $this->errores[] = "⚠️ Error La fecha de desfijación debe ser de $month mes(es) después de la publicación.";
                        return null;
                    }
                    return ['fecha_publicacion' =>  Carbon::instance($fecha_publicacion), 'fecha_desfijacion' =>  Carbon::instance($fecha_desfijacion)];
                } catch (\Exception $e) {
                    $this->errores[] = "⚠️ Error al convertir fechas desde Excel: " . $e->getMessage();
                    return null;
                }
            }
        }
    }
    public function batchSize(): int
    {
        return 1000; // Definir el tamaño del lote para mejorar el rendimiento
    }
    public function chunkSize(): int
    {
        return 1000; // Para evitar cargar demasiados registros en memoria
    }
}
