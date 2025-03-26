<?php
session_start();
require 'conexion.php';
 
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}
 
$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador' && $nivel_acceso != 'adquisicion') {
    header('Location: index.php');
    exit();
}
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha_carga'])) {
    $fecha_carga = $_POST['fecha_carga'];
 
    // Convertir la fecha de carga a formato DateTime
    $fecha_carga_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $fecha_carga);
 
    if ($fecha_carga_datetime) {
        // Consulta para eliminar el registro
        $query = "DELETE FROM MKT_PREPAGO.DBO.BENCH_PP_SV WHERE CONVERT(VARCHAR, FECHA_ACTUALIZACION, 120) = ?";
        $params = array($fecha_carga_datetime->format('Y-m-d H:i:s'));
        $stmt = sqlsrv_query($conn, $query, $params);
 
        if ($stmt === false) {
            $_SESSION['alertMessage'] = 'Error al eliminar el registro. Intente nuevamente.';
            $_SESSION['alertClass'] = 'alert-error';
        } else {
            $_SESSION['alertMessage'] = 'Registro eliminado exitosamente.';
            $_SESSION['alertClass'] = 'alert-success';
        }
        sqlsrv_free_stmt($stmt);
    } else {
        $_SESSION['alertMessage'] = 'Formato de fecha inválido.';
        $_SESSION['alertClass'] = 'alert-error';
    }
} else {
    $_SESSION['alertMessage'] = 'No se recibió una fecha de carga válida.';
    $_SESSION['alertClass'] = 'alert-error';
}
 
header('Location: bench_sv.php');
exit();
?>