<?php
// Ruta al archivo de configuración
$configPath = 'D:\www\pass.php';

// Verificar que el archivo existe
if (!file_exists($configPath)) {
    throw new Exception("El archivo de configuración no existe. Contacta al administrador.");
}

// Cargar la configuración
$config = include($configPath);

// Asignar los valores de configuración
$serverName = $config['DB_SERVER'];
$connectionInfo = array(
    "Database" => $config['DB_NAME'],    // Nombre de la base de datos
    "UID" => $config['DB_USER'],         // Usuario
    "PWD" => $config['DB_PASSWORD'],     // Contraseña
    "TrustServerCertificate" => true, 
    "CharacterSet" => "UTF-8"
);

// Intentar conectar al servidor de la base de datos
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    // Capturar el error de conexión
    $errors = sqlsrv_errors();
    
    // Registrar el error en un archivo de log (opcional)
    error_log(print_r($errors, true), 3, "error_log.txt");
    
    // Lanzar una excepción con un mensaje de error amigable
    throw new Exception("No se pudo conectar a la base de datos. Por favor, inténtelo más tarde.");
}
?>