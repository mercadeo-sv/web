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
date_default_timezone_set('America/El_Salvador'); 

$userUltMod = $_SESSION['nombre_usuario'];
$fechaActualizacion = date('Y-m-d H:i:s');

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador' && $nivel_acceso != 'planeacion') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excelFile'])) {
    $fileName = $_FILES['excelFile']['tmp_name'];
    $nombreArchivo = $_FILES['excelFile']['name'];

    if ($_FILES['excelFile']['size'] > 0) {
        $spreadsheet = IOFactory::load($fileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $required_keys = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        $valid = true;

        foreach (array_slice($sheetData, 1) as $row) {
            foreach ($required_keys as $key) {
                if (!array_key_exists($key, $row)) {
                    $valid = false;
                    break 2;
                }
            }
        }

        if (!$valid) {
            $_SESSION['alertMessage'] = 'El archivo seleccionado no es válido, favor contacta al administrador';
            $_SESSION['alertClass'] = 'alert-error';
            header('Location: existencias.php');
            exit();
        }

        try {
            sqlsrv_begin_transaction($conn);
            $sql = "INSERT INTO MKT_PREPAGO.DBO.TBL_EXISTENCIAS (CENTRO, CANAL_VTAS, NOMBRE_BODEGA, ALMACEN, DENOM_ALMACEN, MATERIAL, ARTICULO_DESPACHO, CANTIDAD, LOTE, USER_MOD, FECHA_ACTUALIZACION, NOM_ARCHIVO) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = sqlsrv_prepare($conn, $sql, array(&$CENTRO, &$CANAL_VTAS, &$NOMBRE_BODEGA, &$ALMACEN, &$DENOM_ALMACEN, &$MATERIAL, &$ARTICULO_DESPACHO, &$CANTIDAD, &$LOTE, &$userUltMod, &$fechaActualizacion, &$nombreArchivo));

            foreach (array_slice($sheetData, 1) as $row) {
                $CENTRO = $row['A'];
                $CANAL_VTAS = $row['B'];
                $NOMBRE_BODEGA = $row['C'];
                $ALMACEN = $row['D'];
                $DENOM_ALMACEN = $row['E'];
                $MATERIAL = $row['F'];
                $ARTICULO_DESPACHO = $row['G'];

                // Reemplaza comas y elimina otros caracteres no numéricos
                $cantidadLimpia = preg_replace('/[^0-9.,-]/', '', $row['H']);
                $cantidadLimpia = str_replace(',', '', $cantidadLimpia);
                $CANTIDAD = is_numeric($cantidadLimpia) ? (float)$cantidadLimpia : 0;

                $LOTE = $row['I'];

                if (!sqlsrv_execute($stmt)) {
                    throw new Exception(print_r(sqlsrv_errors(), true));
                }
            }

            sqlsrv_commit($conn);
            $_SESSION['alertMessage'] = 'Archivo subido y datos insertados exitosamente.';
            $_SESSION['alertClass'] = 'alert-success';
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            $_SESSION['alertMessage'] = 'Error al subir el archivo: ' . $e->getMessage();
            $_SESSION['alertClass'] = 'alert-error';
        }
    } else {
        $_SESSION['alertMessage'] = 'Error al subir el archivo: El archivo está vacío.';
        $_SESSION['alertClass'] = 'alert-error';
    }
} else {
    $_SESSION['alertMessage'] = 'No se ha subido ningún archivo';
    $_SESSION['alertClass'] = 'alert-error';
}

header('Location: existencias.php');
exit();
?>
