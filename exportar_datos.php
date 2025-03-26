<?php
session_start();

if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Establecer la zona horaria a la deseada
date_default_timezone_set('America/El_Salvador'); // Cambia 'America/El_Salvador' a tu zona horaria si es diferente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoBase = $_POST['tipoBase'];
    $nombreUsuario = $_SESSION['nombre_usuario'];
    $ipUsuario = $_SERVER['REMOTE_ADDR'];
    $fechaDescarga = new DateTime();

    // Incluir el archivo de conexión
    include 'conexion.php';

    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Selección del procedimiento almacenado y nombre de archivo según $tipoBase
    if ($tipoBase === 'Base Clientes Tarjeteros') {
        $sql = "{CALL MKT_PREPAGO.[dbo].[SP_USUARIOS_PIN]}";
        $nombreArchivo = 'Base_Clientes_Tarjeteros.csv';
    } elseif ($tipoBase === 'Base Superpack Express') {
        $sql = "{CALL MKT_PREPAGO.[dbo].[SP_USUARIOS_SUPERPACK]}";
        $nombreArchivo = 'Base_Superpack_Express.csv';
    } elseif ($tipoBase === 'Base Migración 3G') {
        $sql = "{CALL MKT_PREPAGO.[dbo].[SP_USUARIOS_MIGRACION3G]}";
        $nombreArchivo = 'Base_Migracion_SIM3G.csv';
    } else {
        die('Tipo de base no reconocido.');
    }

    // Ejecución del procedimiento almacenado
    $stmt = sqlsrv_query($conn, $sql);

    // Verificar si la consulta fue exitosa
    if ($stmt === false) {
        // Mostrar errores de SQL Server si la consulta falla
        die(print_r(sqlsrv_errors(), true));
    }

    // Creación del archivo CSV sin comas (tabulado con espacios)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    $output = fopen('php://output', 'w');
    
    // Escribir los encabezados de las columnas, separados por espacios
    fwrite($output, "Email PhoneNumber FirstName LastName ZIPPostal Code City StateProvince Country DateofBirth Gender Age Value AdditionalServices CustomerType PaymentMethod\n");

    // Escribir cada fila de resultados, separando las columnas por espacios
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Convertir el array a una cadena con separación por espacios
        $line = implode(' ', $row);
        fwrite($output, $line . "\n");
    }

    fclose($output);

    // Información para insertar en CONTROL_VERSIONES
    $nombreBase = $_POST['tipoBase'];
    $nombreUsuario = $_SESSION['nombre_usuario'];  // Nombre de usuario que se guarda en la sesión
    $ipUsuario = $_SERVER['REMOTE_ADDR'];  // Obtiene la IP del usuario
    $fechaDescarga = date('Y-m-d H:i:s');  // Fecha y hora actual

    // Consulta de inserción en CONTROL_VERSIONES
    $query = "INSERT INTO MKT_PREPAGO.[dbo].[CONTROL_VERSIONES] (nombre_base, nombre_usuario, ip, fecha_descarga)
              VALUES (?, ?, ?, ?)";

    // Preparar y ejecutar la consulta
    $params = array($nombreBase, $nombreUsuario, $ipUsuario, $fechaDescarga);
    $stmt = sqlsrv_query($conn, $query, $params);

    // Verificar si la inserción fue exitosa
    if ($stmt === false) {
        // Capturar el error y mostrarlo (opcional, para depuración)
        die(print_r(sqlsrv_errors(), true));
    } else {
        // Liberar el statement y cerrar la conexión
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
    }
}
?>
