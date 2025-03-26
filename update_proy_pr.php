<?php
session_start();
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
    $proyPr30 = $_POST['proyPr30'];
    $proyAltas = $_POST['proyAltas'];
    $proyRecuperados = $_POST['proyRecuperados'];
    $proySalidas = $_POST['proySalidas'];
    $proyParqueDiario = $_POST['proyParqueDiario'];
    $userUltMod = $_SESSION['nombre_usuario'];
    $fechaActualizacion = date('Y-m-d H:i:s'); // Formato de fecha y hora actual
    $tipoCambio = 'parque';

    $sql = "UPDATE MKT_PREPAGO.DBO.PROYECCION 
            SET PR30_VARIABLE = ?, PROY_ALTAS = ?, PROY_RECUPERADOS = ?, PROY_SALIDAS = ?, PROY_PARQUE_DIARIO = ?, fecha_actualizacion = ?, user_ult_mod = ?, tipo_mod = ?
            WHERE IDCALENDARIO = ?";
    $params = array($proyPr30, $proyAltas, $proyRecuperados, $proySalidas, $proyParqueDiario, $fechaActualizacion, $userUltMod, $tipoCambio, $idCalendario);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    $_SESSION['alertMessage'] = 'Proyección modificada';
    header('Location: forecast_pr.php');
    exit();
}
?>
