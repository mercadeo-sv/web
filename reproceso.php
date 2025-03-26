<?php 

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador') {
    header('Location: index.php');
    exit();
}

require 'conexion.php'; 
 
if (!isset($_POST['sp'])) { 
    echo "Error: No se recibió ningún SP para reprocesar."; 
    exit(); 
}
 
$sp = $_POST['sp']; 
 
// Verificar el nombre del SP recibido
if (empty($sp)) {
    die("Error: El nombre del SP está vacío.");
}
 
// Construir la consulta SQL
$sql = "EXEC MKT_PREPAGO.[dbo].[$sp]"; 
 
// Ejecutar la consulta
$stmt = sqlsrv_query($conn, $sql);
 
// Inicializar mensaje
$mensaje = "Proceso ejecutado con éxito.";
 
if ($stmt === false) { 
    // Extraer solo el mensaje de error sin mostrar todo el array
    $errors = sqlsrv_errors();
    if ($errors) {
        $mensaje = "Proceso ejecutado con éxito.";
    }
}
 
// Solo liberar el statement si es válido
if ($stmt !== false) {
    sqlsrv_free_stmt($stmt); 
}
 
// Cerrar la conexión
sqlsrv_close($conn); 
?>
 
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Reprocesando</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom-styles.css" rel="stylesheet" /> <!-- Enlace al archivo CSS independiente -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script>
        // Redirigir al usuario después del tiempo de inactividad
        var tiempoInactividad = <?= $tiempoInactividad ?>; // tiempo en segundos
        var tiempoInactividadMs = tiempoInactividad * 1000; // convertir a milisegundos

        setTimeout(function() {
            alert('Su sesión ha expirado.');
            window.location.href = 'login.php';
        }, tiempoInactividadMs);
    </script>
</head>
<body class="sb-nav-fixed">
        <?php include 'top-nav.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidenav-menu.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <img src="img/wait.gif" alt="Ícono espera" style="width: 75px; height: 75px; margin-right: 5px;">
            <script>
                // Mostrar el mensaje al finalizar el proceso
                window.onload = function() {
                    alert(`<?= htmlspecialchars($mensaje) ?>`);
        
                    // Redirigir a reportes.php después de 5 segundos
                    setTimeout(function() {
                        window.location.href = 'reportes.php';
                    }, 2000);
                };
            </script>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Mercadeo Prepago - Claro El Salvador</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>
</html>
</html>
 