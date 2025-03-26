<?php
session_start();
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}


// Establecer la zona horaria a la deseada
date_default_timezone_set('America/El_Salvador'); // Cambia 'America/El_Salvador' a tu zona horaria si es diferente

// variables dinamicas
    $userUltMod = $_SESSION['nombre_usuario'];
    $fechaActualizacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual
    $tipoCambio = 'Carga Proy';
    $nombreArchivo = $_FILES['excelFile']['name']; // Capturar el nombre del archivo

// Tiempo de inactividad en segundos
$tiempoInactividad = 900;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $tiempoInactividad)) {
    // El último acceso fue hace más de $tiempoInactividad segundos
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // actualizar el tiempo de última actividad

$nivel_acceso = $_SESSION['nivel_acceso'];

if ($nivel_acceso != 'administrador' && $nivel_acceso != 'adquisicion') {
    header('Location: index.php');
    exit();
}

include 'conexion.php';
require 'autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['excelFile']['tmp_name'];
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $sql = "INSERT INTO MKT_PREPAGO.DBO.PROYECCION (
                    FECHA, IDPERIODO, IDCALENDARIO, PROY, PROY_PR30, PR30_VARIABLE, PROY_ALTAS, 
                    PROY_RECUPERADOS, PROY_SALIDAS, PROY_KIT, PROY_CHIP, PROY_INT_MOVIL, 
                    PROY_LFI, PROY_PORTIN, PROY_PARQUE_DIARIO, FECHA_ACTUALIZACION, USER_ULT_MOD, TIPO_MOD, NOM_ARCHIVO
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $error_occurred = false;

        foreach ($rows as $index => $row) {
            if ($index == 0) { // Skip header row
                continue;
            }

            if (empty($row[0])) {
                continue;
            }

            $params = array(
                $row[0], // FECHA
                (int) $row[1], // IDPERIODO
                (int) $row[2], // IDCALENDARIO
                (float) $row[3], // PROY
                (int) $row[4], // PROY_PR30
                (int) $row[5], // PR30_VARIABLE
                (int) $row[6], // PROY_ALTAS
                (int) $row[7], // PROY_RECUPERADOS
                (int) $row[8], // PROY_SALIDAS
                (int) $row[9], // PROY_KIT
                (int) $row[10], // PROY_CHIP
                (int) $row[11], // PROY_INT_MOVIL
                (int) $row[12], // PROY_LFI
                (int) $row[13], // PROY_PORTIN
                (int) $row[14], // PROY_PARQUE_DIARIO
                $fechaActualizacion,
                $userUltMod,
                $tipoCambio,
                $nombreArchivo
            );

            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) {
                $error_occurred = true;
                break;
            }
            sqlsrv_free_stmt($stmt);
        }

        sqlsrv_close($conn);

        if ($error_occurred) {
            $_SESSION['alertMessage'] = 'Error al subir el archivo';
        } else {
            $_SESSION['alertMessage'] = 'Datos cargados exitosamente';
        }

        header('Location: proy_mes.php');
        exit();
    } else {
        $_SESSION['alertMessage'] = 'Error al subir el archivo';
        header('Location: proy_mes.php');
        exit();
    }
}
?>
