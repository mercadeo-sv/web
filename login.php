<?php
session_start();


// Establecer la zona horaria a la deseada
date_default_timezone_set('America/El_Salvador'); // Cambia 'America/El_Salvador' a tu zona horaria si es diferente

try {
    include 'conexion.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre_usuario = $_POST['nombre_usuario'];
        $contraseña = $_POST['contraseña'];

        $sql = "exec mkt_prepago..sp_select_user_login ?";
        $params = array($nombre_usuario);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt && sqlsrv_has_rows($stmt)) {
            $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if ($user['bloqueado']) {
                $error = "Usuario bloqueado por demasiados intentos fallidos.";
            } elseif ($contraseña === $user['contraseña']) { // En un entorno de producción, usa password_verify
                $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                $_SESSION['nivel_acceso'] = $user['nivel_acceso'];
                $_SESSION['nombre_apellido'] = $user['nombre_apellido'];
                $_SESSION['LAST_ACTIVITY'] = time(); // registrar el tiempo de inicio de sesión

                // Restablecer intentos fallidos
                $reset_sql = "exec mkt_prepago..sp_reset_connection_user ?";
                sqlsrv_query($conn, $reset_sql, $params);

                // Insertar el log de éxito en la tabla de logs
                $sql_insert_login = "exec mkt_prepago..sp_insert_login_user ?, ?, ?";
                $insert_login_params = array($nombre_usuario, date('Y-m-d H:i:s'), 1); // 1 = Login correcto
                sqlsrv_query($conn, $sql_insert_login, $insert_login_params);

                header('Location: index.php');
                exit();
            } else {
                // Incrementar el contador de intentos fallidos
                $user['intentos_fallidos']++;
                if ($user['intentos_fallidos'] >= 3) {
                    $user['bloqueado'] = 1;
                    $error = "Usuario bloqueado por demasiados intentos fallidos.";
                } else {
                    $error = "Nombre de usuario o contraseña incorrectos.";
                }

                // Actualizar intentos fallidos y estado de bloqueo en la base de datos
                $update_sql = "exec mkt_prepago..sp_update_status_user ?,?,?";
                $update_params = array($user['intentos_fallidos'], $user['bloqueado'], $nombre_usuario);
                sqlsrv_query($conn, $update_sql, $update_params);

                // Insertar el log de fallo en la tabla de logs
                $sql_insert_login = "exec mkt_prepago..sp_insert_login_user ?, ?, ?";
                $insert_login_params = array($nombre_usuario, date('Y-m-d H:i:s'), 0); // 0 = Login fallido
                sqlsrv_query($conn, $sql_insert_login, $insert_login_params);
            }
        } else {
            $error = "Nombre de usuario o contraseña incorrectos.";

            // Insertar el log de fallo en la tabla de logs
            $sql_insert_login = "exec mkt_prepago..sp_insert_login_user ?, ?, ?";
            $insert_login_params = array($nombre_usuario, date('Y-m-d H:i:s'), 0); // 0 = Login fallido
            sqlsrv_query($conn, $sql_insert_login, $insert_login_params);
        }

        sqlsrv_free_stmt($stmt);
    }
} catch (Exception $e) {
    // Mostrar el mensaje de error amigable
    $error = $e->getMessage();
} finally {
    // Verificar si la conexión es válida antes de cerrarla
    if ($conn && get_resource_type($conn) === 'SQL Server Connection') {
        sqlsrv_close($conn);
    }
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
    <title>Portal Mercadeo</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #EF233C;
        }
    </style>
</head>
<body>
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">
                                        <img src="logo_claro.png" style="width: 50px; height: 50px;">
                                        Portal Mercadeo Prepago
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <form action="login.php" method="POST">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputEmail" name="nombre_usuario" type="text" placeholder="name@example.com" required />
                                            <label for="inputEmail">Usuario</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputPassword" name="contraseña" type="password" placeholder="Password" required />
                                            <label for="inputPassword">Contraseña</label>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4 mb-0">
                                            <button class="btn btn-success" type="submit">
                                                <img src="img/login.png" alt="Login Icon" style="width: 24px; height: 24px; margin-right: 8px;">
                                                Login
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger mt-3"><?= $error ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($error)) echo $error; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <?php if (isset($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>
