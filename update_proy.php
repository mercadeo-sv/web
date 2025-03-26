<?php
include 'conexion.php';

session_start();
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Establecer la zona horaria a la deseada
date_default_timezone_set('America/El_Salvador'); // Cambia 'America/El_Salvador' a tu zona horaria si es diferente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCalendario = $_POST['idCalendario'];
    $proy = $_POST['proy'];
    $userUltMod = $_SESSION['nombre_usuario'];
    $fechaActualizacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual
    $tipoCambio = 'consumo';

    // Verificar que $proy sea un valor decimal válido
    if (!is_numeric($proy)) {
        $_SESSION['alertMessage'] = 'Valor de proyección inválido';
        header('Location: forecast.php');
        exit();
    }

    $sql = "UPDATE MKT_PREPAGO.DBO.PROYECCION 
            SET PROY = ?, 
                fecha_actualizacion = ?, 
                user_ult_mod = ?,
                TIPO_MOD = ?
            WHERE IDCALENDARIO = ?";
    
    $params = array($proy, $fechaActualizacion, $userUltMod, $tipoCambio, $idCalendario);

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    $_SESSION['alertMessage'] = 'Proyección modificada';
    header('Location: forecast.php');
    exit();
}
?>
