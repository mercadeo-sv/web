<?php
session_start();
require 'conexion.php';
require 'autoload.php';
 
use PhpOffice\PhpSpreadsheet\IOFactory;
 
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $fileName = $_FILES['excelFile']['tmp_name'];
    $fileNameOriginal = $_FILES['excelFile']['name'];
    $modUser = $_SESSION['nombre_usuario'];
    $fechaActualizacion = new DateTime('now', new DateTimeZone('America/El_Salvador'));
 
    try {
        $spreadsheet = IOFactory::load($fileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        $firstRow = true; // Bandera para identificar la primera fila (encabezados)
 
        foreach ($worksheet->getRowIterator() as $row) {
            if ($firstRow) {
                $firstRow = false; // Omite la primera fila (encabezados)
                continue;
            }
 
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        }
 
        $insertQuery = "INSERT INTO [MKT_PREPAGO].[dbo].[BENCH_PP_SV] ([FECHA], [MATERIAL], [ARTICULO_DESPACHO], [RAM], [ROM], [PANTALLA], [CAMARA_PPAL], [CAMARA_FTL], [BATERIA], [PRECIO], [INVENTARIO], [OPERADOR], [CATEGORIA], [URL], [OFERTA], [MOD_USER], [NOM_ARCHIVO], [FECHA_ACTUALIZACION]) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
 
        foreach ($data as $row) {
            if (count($row) < 15) {
                continue;
            }
 
            $params = [
                $fechaActualizacion,           // FECHA_ACTUALIZACION
                intval($row[0]),               // MATERIAL
                $row[1],                       // ARTICULO_DESPACHO
                $row[2],                       // RAM
                $row[3],                       // ROM
                $row[4],                       // PANTALLA
                $row[5],                       // CAMARA_PPAL
                $row[6],                       // CAMARA_FTL
                $row[7],                       // BATERIA
                floatval($row[8]),             // PRECIO
                floatval($row[9]),             // INVENTARIO
                $row[10],                      // OPERADOR
                $row[11],                      // CATEGORIA
                $row[12],                      // URL
                intval($row[13]),              // OFERTA
                $modUser,                      // MOD_USER
                $fileNameOriginal,             // NOM_ARCHIVO
                $fechaActualizacion            // FECHA_ACTUALIZACION
            ];
 
            $stmt = sqlsrv_query($conn, $insertQuery, $params);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
        }
 
        sqlsrv_free_stmt($stmt);
        $_SESSION['alertMessage'] = "Archivo subido y datos insertados con éxito.";
        $_SESSION['alertClass'] = "alert-success";
 
    } catch (Exception $e) {
        $_SESSION['alertMessage'] = "Error al procesar el archivo: " . $e->getMessage();
        $_SESSION['alertClass'] = "alert-error";
    }
}
 
header('Location: bench_sv.php');
exit();
?>