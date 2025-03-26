<?php
/* Válida el tiempo de sesión límite */
include 'politicas_sesion.php';
include 'conexion.php';

// Manejo de acciones de usuario
$alertMessage = '';
$alertClass = 'alert-success'; // Clase CSS predeterminada para las alertas

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $userId = $_POST['user_id'] ?? null;

        if ($action === 'change_password') {
            $newPassword = $_POST['new_password'];

            // Ejecutar el procedimiento almacenado
            $sql = "exec mkt_prepago..sp_change_password_user ?,?";
            $params = [$newPassword, $userId];
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                // Capturar el error de SQL Server
                $errors = sqlsrv_errors();
                $errorCode = $errors[0]['code'] ?? 'Desconocido';
                $errorMessage = $errors[0]['message'] ?? 'Error desconocido en el servidor.';

                // Mostrar un mensaje amigable al usuario
                $alertClass = 'alert-danger';
                $alertMessage = "Error al actualizar la contraseña (Código: $errorCode). Comuníquese con el administrador.";
            } else {
                $alertMessage = 'Contraseña actualizada correctamente.';
            }
        }
    }
}

// Usuario que se muestra
$nombreUsuario = $_SESSION['nombre_usuario']; // Nombre de usuario que se guarda en la sesión

// Obtener usuarios de la base de datos
$sql = "exec mkt_prepago..sp_select_user_login ?";
$params = [$nombreUsuario];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $alertClass = 'alert-danger';
    $alertMessage = "Error al cargar usuarios. Comuníquese con el administrador.";
    $usuarios = [];
} else {
    $usuarios = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $usuarios[] = $row;
    }
}
sqlsrv_free_stmt($stmt);
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
    <title>Mantenimiento de Usuarios</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom-styles.css" rel="stylesheet" /> <!-- Enlace al archivo CSS independiente -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Estilos de AlertifyJS -->
    <link href="css/styles-alertify.css" rel="stylesheet" /> 
    <link rel="stylesheet" href="assets/alertifyjs/css/alertify.min.css">
    <link rel="stylesheet" href="assets/alertifyjs/css/themes/default.min.css">
    <script src="assets/alertifyjs/alertify.min.js"></script>
    <script>
    setTimeout(function() {
        alertify.alert('Aviso', 'Su sesión ha expirado.',
        function() {
            window.location.href = 'login.php';}
        );
    }, <?= $tiempoInactividad * 1000 ?>);
    </script>
    <style>
        .btn-block, .btn-unlock, .btn-change-password, .btn-delete {
            font-size: 12px;
            padding: 5px 10px;
        }

        .btn-block {
            background-color: red;
            color: white;
        }

        .btn-unlock {
            background-color: orange;
            color: white;
        }

        .btn-change-password {
            background-color: green;
            color: white;
        }

        .btn-delete {
            background-color: darkred;
            color: white;
        }

        .btn-block:hover, .btn-unlock:hover, .btn-change-password:hover, .btn-delete:hover {
            opacity: 0.8;
        }

        .alert-info {
            background-color: orange;
            color: white;
        }

        .alert-success {
            background-color: green;
            color: white;
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
                    <h1 class="mt-4"></h1>
                    <?php if ($alertMessage): ?>
                        <div class="alert <?= $alertClass ?>" id="alertMessage"><?= $alertMessage ?></div>
                    <?php endif; ?>
                    <!-- Tabla de Usuarios -->
                    <h2 class="mt-5"></h2>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-8">
                                <div class="card-header">Cambio de Contraseña</div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Nombre de Usuario</th>
                                                    <th>Nombre Completo</th>
                                                    <th>Nivel de Permisos</th>
                                                    <th>Actualizar Contraseña</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($usuarios as $usuario): ?>
                                                <tr>
                                                    <td><?= $usuario['nombre_usuario'] ?></td>
                                                    <td><?= $usuario['nombre_apellido'] ?></td>
                                                    <td><?= $usuario['nivel_acceso'] ?></td>
                                                    <td>
                                                        <form method="post" action="">
                                                            <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>">
                                                            <input type="hidden" name="action" value="change_password">
                                                            <input type="password" name="new_password" placeholder="Escribe una contraseña" required>
                                                            <button type="submit" class="btn btn-change-password">Cambiar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                     </div>
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>                      
            </main>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
    <script>
        // Función para ocultar la alerta después de 2 segundos
        setTimeout(function() {
            var alertMessage = document.getElementById('alertMessage');
            if (alertMessage) {
                alertMessage.style.display = 'none';
            }
        }, 2000);

        // Manejar el evento del modal de confirmación para eliminar usuario
        var confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var userName = button.getAttribute('data-user-name');

            var modalBody = confirmDeleteModal.querySelector('.modal-body #userToDelete');
            modalBody.textContent = userName;

            var modalUserId = confirmDeleteModal.querySelector('.modal-footer #userIdToDelete');
            modalUserId.value = userId;
        });
    </script>
</body>
</html>
