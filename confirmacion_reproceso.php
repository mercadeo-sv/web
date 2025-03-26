<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

include 'conexion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador') {
    header('Location: index.php');
    exit();
}

// Verifica si se recibe el parámetro SP
$sp = isset($_GET['sp']) ? $_GET['sp'] : null;
if (!$sp) {
    echo "Error: No se proporcionó ningún SP para reprocesar.";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Confirmar Reproceso</title>
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
        <style>
        /* Estilos para centrar y reducir el tamaño del main */
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        main {
            width: 400px;
            padding: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body class="sb-nav-fixed">
        <?php include 'top-nav.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidenav-menu.php'; ?>
        <div id="layoutSidenav_content">
        <main>
        <div class="card mb-4">
            <div class="card-header">Confirmación</div>
            <div class="card-body">
                <form action="reproceso.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">¿Está seguro de reprocesar el reporte: <b><?= htmlspecialchars($sp) ?></b>?</label>
                    </div>
                    <input type="hidden" name="sp" value="<?= htmlspecialchars($sp) ?>">
                    <div class="d-flex justify-content-center mt-4 mb-0">
                    <button class="btn btn-success d-flex align-items-center" type="submit"> Reprocesar </button>
                    </div>
                </form>
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
