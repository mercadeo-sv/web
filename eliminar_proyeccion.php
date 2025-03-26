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

    if ($fecha_carga) {
        $query = "DELETE FROM MKT_PREPAGO.DBO.PROYECCION WHERE IDPERIODO = ?";
        $params = array($fecha_carga);

        $stmt = sqlsrv_query($conn, $query, $params);
        if ($stmt === false) {
            $_SESSION['alertMessage'] = 'Error al eliminar el registro. Intente nuevamente.';
            $_SESSION['alertClass'] = 'alert-error';
        } else {
            $_SESSION['alertMessage'] = 'Registro eliminado exitosamente.';
            $_SESSION['alertClass'] = 'alert-success';
        }
        sqlsrv_free_stmt($stmt);
    } 
}

header('Location: proy_mes.php');
exit();
?>
