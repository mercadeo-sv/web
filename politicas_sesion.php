
<?php
session_start();
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Tiempo de inactividad en segundos
$tiempoInactividad = 600; // 600 = 10 Minutos con sesión activa

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $tiempoInactividad)) {
    // El último acceso fue hace más de $tiempoInactividad segundos
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // actualizar el tiempo de última actividad

$nivel_acceso = $_SESSION['nivel_acceso'];
$nombre_apellido = $_SESSION['nombre_apellido'];

?>

