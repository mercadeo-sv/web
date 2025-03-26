<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

require 'conexion.php';

date_default_timezone_set('America/El_Salvador'); // Zona horaria de El Salvador

// Variables del usuario y la fecha
$userUltMod = $_SESSION['nombre_usuario'];
$fechaActualizacion = date('Y-m-d H:i:s'); // Fecha y hora actuales

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['txtFile'])) {
    $fileName = $_FILES['txtFile']['tmp_name'];
    $nombreArchivo = $_FILES['txtFile']['name']; // Capturar el nombre del archivo subido

    // Verificar si el archivo tiene contenido
    if ($_FILES['txtFile']['size'] > 0) {
        // Abrir el archivo de texto
        $file = fopen($fileName, "r");
        if ($file !== false) {
            try {
                // Iniciar la transacción
                sqlsrv_begin_transaction($conn);

                // SQL para insertar en la tabla prepago_cav_sv_comercial
                $sql = "exec mkt_prepago..sp_insert_tbl_cav ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
                
                // Preparar la sentencia SQL
                $stmt = sqlsrv_prepare($conn, $sql, array(&$idpais, &$idperiodo, &$fecha_calendario, &$telefono, &$idplan, &$estatus, &$canal, &$producto, &$servicio, &$id_seg_antiguedad, &$seg_antiguedad, &$promedio_recarga, &$id_segmentacion_valor, &$segmentacion_valor, &$userUltMod, &$nombreArchivo, &$fechaActualizacion));

                // Leer cada línea del archivo de texto
                while (($line = fgets($file)) !== false) {
                    // Separar los valores por el delimitador |
                    $data = explode("|", trim($line));

                    // Validar que la línea tiene el número correcto de columnas
                    if (count($data) === 14) {
                        $idpais = $data[0];
                        $idperiodo = $data[1];
                        $fecha_calendario = $data[2];  // Se asume que el formato es YYYYMMDD
                        $telefono = $data[3];
                        $idplan = $data[4];
                        $estatus = $data[5];
                        $canal = $data[6];
                        $producto = $data[7];
                        $servicio = $data[8];
                        $id_seg_antiguedad = $data[9];
                        $seg_antiguedad = $data[10];
                        $promedio_recarga = $data[11];
                        $id_segmentacion_valor = $data[12];
                        $segmentacion_valor = $data[13];

                        // Ejecutar la inserción
                        if (!sqlsrv_execute($stmt)) {
                            throw new Exception(print_r(sqlsrv_errors(), true));
                        }
                    } else {
                        throw new Exception('Formato de archivo incorrecto en la línea: ' . $line);
                    }
                }

                // Commit de la transacción
                sqlsrv_commit($conn);
                $_SESSION['alertMessage'] = 'Archivo subido y datos insertados exitosamente.';
                $_SESSION['alertClass'] = 'alert-success';

            } catch (Exception $e) {
                // Si ocurre un error, revertir la transacción
                sqlsrv_rollback($conn);
                $_SESSION['alertMessage'] = 'Error al subir el archivo: ' . $e->getMessage();
                $_SESSION['alertClass'] = 'alert-error';
            }

            // Cerrar el archivo
            fclose($file);
        } else {
            $_SESSION['alertMessage'] = 'Error al abrir el archivo.';
            $_SESSION['alertClass'] = 'alert-error';
        }
    } else {
        $_SESSION['alertMessage'] = 'Error al subir el archivo: El archivo está vacío.';
        $_SESSION['alertClass'] = 'alert-error';
    }
} else {
    $_SESSION['alertMessage'] = 'No se ha subido ningún archivo.';
    $_SESSION['alertClass'] = 'alert-error';
}

header('Location: clientes_cav.php');
exit();
?>
