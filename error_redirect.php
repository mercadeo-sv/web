<?php
session_start();
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Tiempo de inactividad en segundos
$tiempoInactividad = 900;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $tiempoInactividad)) {
    // El último acceso fue hace más de $tiempoInactividad segundos
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // actualizar el tiempo de última actividad

$nivel_acceso = $_SESSION['nivel_acceso'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Portal Mercadeo</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom-styles.css" rel="stylesheet" /> <!-- Enlace al archivo CSS independiente -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script>
        // Redirigir al usuario a index.php después de 5 segundos
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
    <style>
        .alert-error {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: red;
            color: white;
            text-align: center;
            padding: 20px;
            margin: 20px;
            border-radius: 5px;
            z-index: 1000; /* Asegura que la alerta esté encima de otros elementos */
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include 'top-nav.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidenav-menu.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <div class="alert alert-error" role="alert">
                        No fue posible cargar la página, favor contacta al administrador.
                    </div>
                </div>
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
