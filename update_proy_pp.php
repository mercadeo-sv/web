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
    $proyKit = $_POST['proyKit'];
    $proyChip = $_POST['proyChip'];
    $proyIntMovil = $_POST['proyIntMovil'];
    $proyLfi = $_POST['proyLfi'];
    $proyPortin = $_POST['proyPortin'];
    $userUltMod = $_SESSION['nombre_usuario'];
    $fechaActualizacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual
    $tipoCambio = 'activaciones';

    $sql = "UPDATE MKT_PREPAGO.DBO.PROYECCION 
            SET PROY_KIT = ?, 
                PROY_CHIP = ?, 
                PROY_INT_MOVIL = ?, 
                PROY_LFI = ?, 
                PROY_PORTIN = ?, 
                fecha_actualizacion = ?, 
                user_ult_mod = ?,
                tipo_mod = ?
            WHERE IDCALENDARIO = ?";
    
    $params = array($proyKit, $proyChip, $proyIntMovil, $proyLfi, $proyPortin, $fechaActualizacion, $userUltMod, $tipoCambio, $idCalendario);

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    session_start();
    $_SESSION['alertMessage'] = 'Proyección modificada';
    header('Location: forecast_pp.php');
    exit();
}
?>
