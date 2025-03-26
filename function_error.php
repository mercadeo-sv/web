<?php

function handleSqlErrors($errors) {
    
    // Establecer la zona horaria a la deseada
    date_default_timezone_set('America/El_Salvador'); // Cambia 'America/El_Salvador' a tu zona horaria si es diferente

    // Fecha y hora del error
    $dateTime = date('Y-m-d H:i:s');
    
    // Archivo donde ocurrió el error
    $page = $_SERVER['PHP_SELF'];
    
    // Usuario actual (si está configurado en la sesión)
    $user = isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'Usuario no autenticado';
    
    // Construye el mensaje de log
    $logMessage = "[$dateTime] Error en la página: $page\n";
    $logMessage .= "Usuario: $user\n";
    $logMessage .= "Detalles del error:\n" . print_r($errors, true) . "\n";
    $logMessage .= "----------------------------------------\n";
    
    // Escribe el mensaje en un archivo de log
    error_log($logMessage, 3, 'sql_errors.log');
    
    // Redirige a una página personalizada
    header('Location: error_redirect.php');
    exit(); // Asegura que el script no continúe ejecutándose
}

?>