<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $articuloDespacho = $_POST['articuloDespacho'];

    $sql = "EXEC [172.23.21.93].PREPAGO.dbo.SP_UPDATE_DIM_MODELOS ?, ?, ?;
    ";
    $params = array($articuloDespacho, $marca, $modelo);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo "<script>alert('Registro actualizado exitosamente'); window.location.href = 'smartphone.php';</script>";
    } else {
        echo "<script>alert('Error en la actualización, contacta al administrador'); window.location.href = 'smartphone.php';</script>";
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
}
?>
