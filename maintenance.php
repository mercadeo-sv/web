<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

$nivel_acceso = $_SESSION['nivel_acceso'];
if ($nivel_acceso != 'administrador' && $nivel_acceso != 'administradorjr') {
    header('Location: index.php');
    exit();
}

include 'conexion.php';

// Manejo de acciones de usuario
$alertMessage = '';
$alertClass = 'alert-info'; // Clase CSS predeterminada para las alertas

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = $_POST['user_id'] ?? null;

            if ($action === 'change_password') {
            $newPassword = $_POST['new_password'];
            $sql = "exec mkt_prepago..sp_change_password_user ?,?";
            $params = array($newPassword, $userId);
            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $alertMessage = 'Contraseña actualizada';
            } elseif ($action === 'unlock_user') {
            $sql = "exec mkt_prepago..sp_unlock_user ?";
            $params = array($userId);
            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $alertMessage = 'Se desbloqueó el usuario';
            $alertClass = 'alert-success'; // Clase CSS para alertas verdes
            } elseif ($action === 'block_user') {
            $sql = "exec mkt_prepago..sp_block_user ?";
            $params = array($userId);
            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $alertMessage = 'Se bloqueó el usuario';
            } elseif ($action === 'change_access_level') {
            // Disable changing access level for "administradorjr"
            if ($nivel_acceso != 'administrador') {
                $alertMessage = 'No tienes permisos para modificar el nivel de acceso';
                $alertClass = 'alert-warning'; // Clase CSS para alertas amarillas
            } else {
                $newAccessLevel = $_POST['new_access_level'];
                $sql = "exec mkt_prepago..sp_change_access_level_user ?,?";
                $params = array($newAccessLevel, $userId);
                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
                }
                $alertMessage = 'Se modificaron los permisos del usuario';
            }
            } elseif ($action === 'create_user') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $accessLevel = $_POST['access_level'];
            $nameSurname = $_POST['name_surname'];
            
            // Verificar si el usuario ya existe
            $checkSql = "exec mkt_prepago..sp_select_user_login ?";
            $checkParams = array($username);
            $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);
            
            if ($checkStmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
                $alertMessage = 'El usuario "'.$username.'" ya existe';
            } else {
                $sql = "exec mkt_prepago..sp_create_new_user ?,?,?,?";
                $params = array($username, $password, $accessLevel, $nameSurname);
                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                $alertMessage = 'Usuario creado exitosamente';
                $alertClass = 'alert-success'; // Clase CSS para alertas verdes
            }
            sqlsrv_free_stmt($checkStmt);
        } elseif ($action === 'delete_user') {
            $sql = "exec mkt_prepago..sp_delete_user ?";
            $params = array($userId);
            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            $alertMessage = 'Usuario eliminado exitosamente';
            $alertClass = 'alert-success'; // Clase CSS para alertas verdes
        }
    }
}

// Obtener usuarios de la base de datos
$sql = "exec mkt_prepago..sp_select_all_user";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$usuarios = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $usuarios[] = $row;
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
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><img src="img/usuarios.png" alt="Icono de teléfono" style="width: 25px; height: 25px;"> Gestión de Usuarios</span>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">Nuevo Usuario <img src="img/agregar-usuario.png" alt="Icono de teléfono" style="width: 24px; height: 24px;"></button>
                        </div>
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Nombre de Usuario</th>
                                        <th>Nombre Completo</th>
                                        <th>Permisos</th>
                                        <th>Contraseña</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?= $usuario['nombre_usuario'] ?></td>
                                        <td><?= $usuario['nombre_apellido'] ?></td>
                                        <td>
                                            <form method="post" action="">
                                                <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>">
                                                <input type="hidden" name="action" value="change_access_level">
                                                <select name="new_access_level" onchange="this.form.submit()">
                                                    <option value="administrador" <?= $usuario['nivel_acceso'] == 'administrador' ? 'selected' : '' ?>>Administrador</option>
                                                    <option value="consumo" <?= $usuario['nivel_acceso'] == 'consumo' ? 'selected' : '' ?>>Consumo</option>
                                                    <option value="adquisicion" <?= $usuario['nivel_acceso'] == 'adquisicion' ? 'selected' : '' ?>>Adquisición</option>
                                                    <option value="planeacion" <?= $usuario['nivel_acceso'] == 'planeacion' ? 'selected' : '' ?>>Planeación</option>
                                                    <option value="publicidad" <?= $usuario['nivel_acceso'] == 'publicidad' ? 'selected' : '' ?>>Publicidad</option>
                                                    <option value="callcenter" <?= $usuario['nivel_acceso'] == 'callcenter' ? 'selected' : '' ?>>Call Center</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="post" action="">
                                                <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>">
                                                <input type="hidden" name="action" value="change_password">
                                                <input type="password" name="new_password" placeholder="Nueva contraseña" required>
                                                <button type="submit" class="btn btn-change-password">Cambiar</button>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if ($usuario['bloqueado']): ?>
                                                <form method="post" action="" style="display:inline-block;">
                                                    <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>">
                                                    <input type="hidden" name="action" value="unlock_user">
                                                    <button type="submit" class="btn btn-unlock">Desbloquear</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" action="" style="display:inline-block;">
                                                    <input type="hidden" name="user_id" value="<?= $usuario['id'] ?>">
                                                    <input type="hidden" name="action" value="block_user">
                                                    <button type="submit" class="btn btn-block">Bloquear</button>
                                                </form>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-delete" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-user-id="<?= $usuario['id'] ?>" data-user-name="<?= $usuario['nombre_usuario'] ?>">Eliminar</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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

    <!-- Modal para crear nuevo usuario -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="create_user">
                        <div class="mb-3">
                            <label for="name_surname" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="name_surname" name="name_surname" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="access_level" class="form-label">Nivel de Acceso</label>
                            <select class="form-select" id="access_level" name="access_level" required>
                                <option value="administrador">Administrador</option>
                                <option value="consumo">Consumo</option>
                                <option value="adquisicion">Adquisición</option>
                                <option value="planeacion">Planeación</option>
                                <option value="publicidad">Publicidad</option>
                                <option value="callcenter">Call Center</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-center mt-4 mb-0">
                            <button type="submit" class="btn btn-success">Crear Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Eliminar Usuario -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro que deseas eliminar el usuario <span id="userToDelete"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="user_id" id="userIdToDelete">
                        <input type="hidden" name="action" value="delete_user">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
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
