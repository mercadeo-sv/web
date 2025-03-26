<?php
session_start();
require 'conexion.php';
require 'autoload.php'; // Asegúrate de tener PHPExcel autoload

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Establecer la zona horaria a la deseada
date_default_timezone_set('America/El_Salvador'); // Cambia 'America/El_Salvador' a tu zona horaria si es diferente

// Variables dinámicas
$userUltMod = $_SESSION['nombre_usuario'];
$fechaActualizacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador' && $nivel_acceso != 'adquisicion') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    $fileName = $_FILES['excelFile']['tmp_name'];
    $nombreArchivo = $_FILES['excelFile']['name']; // Capturar el nombre del archivo

    if ($_FILES['excelFile']['size'] > 0) {
        $spreadsheet = IOFactory::load($fileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Verificar que todas las claves requeridas existen
        $required_keys = ['A' => 'FECHA', 'B' => 'DEPTO', 'C' => 'CLARO', 'D' => 'TIGO', 'E' => 'MOVISTAR', 'F' => 'DIGICEL'];
        $valid = true;

        // Validar la primera fila (encabezados)
        $header = $sheetData[1];
        foreach ($required_keys as $key => $value) {
            if (!array_key_exists($key, $header) || trim($header[$key]) != $value) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            $_SESSION['alertMessage'] = 'El archivo seleccionado no es válido, favor contacta al administrador.';
            $_SESSION['alertClass'] = 'alert-error'; // Clase CSS para el mensaje de error
            header('Location: marketshare.php');
            exit();
        }

        try {
            sqlsrv_begin_transaction($conn);
            $sql = "INSERT INTO MKT_PREPAGO.DBO.TBL_MARKETSHARE_MKT (FECHA, DEPTO, CLARO, TIGO, MOVISTAR, DIGICEL, MOD_USER, NOM_ARCHIVO, FECHA_ACTUALIZACION) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = sqlsrv_prepare($conn, $sql, array(&$fecha, &$depto, &$claro, &$tigo, &$movistar, &$digicel, &$userUltMod, &$nombreArchivo, &$fechaActualizacion));

            // Ignorar la primera fila (títulos de las columnas)
            foreach (array_slice($sheetData, 1) as $row) {
                // Manejar la fecha correctamente, ya sea como número o como cadena
                if (is_numeric($row['A'])) {
                    $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['A'])->format('Y-m-d');
                } else {
                    $fecha = date('Y-m-d', strtotime($row['A']));
                }
                $depto = $row['B'];
                $claro = is_numeric($row['C']) ? (float)$row['C'] : 0;
                $tigo = is_numeric($row['D']) ? (float)$row['D'] : 0;
                $movistar = is_numeric($row['E']) ? (float)$row['E'] : 0;
                $digicel = is_numeric($row['F']) ? (float)$row['F'] : 0;

                if (!sqlsrv_execute($stmt)) {
                    throw new Exception(print_r(sqlsrv_errors(), true));
                }
            }

            sqlsrv_commit($conn);
            $_SESSION['alertMessage'] = 'Archivo subido y datos insertados exitosamente.';
            $_SESSION['alertClass'] = 'alert-success'; // Clase CSS para el mensaje de éxito
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            $_SESSION['alertMessage'] = 'Error al subir el archivo: ' . $e->getMessage();
            $_SESSION['alertClass'] = 'alert-error'; // Clase CSS para el mensaje de error
        }
    } else {
        $_SESSION['alertMessage'] = 'Error al subir el archivo: El archivo está vacío.';
        $_SESSION['alertClass'] = 'alert-error'; // Clase CSS para el mensaje de error
    }
} else {
    $_SESSION['alertMessage'] = 'No se ha subido ningún archivo.';
    $_SESSION['alertClass'] = 'alert-error'; // Clase CSS para el mensaje de error
}

header('Location: marketshare.php');
exit();
?>
