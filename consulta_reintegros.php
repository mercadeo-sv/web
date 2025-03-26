<?php 

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

if ($nivel_acceso != 'administrador' && $nivel_acceso != 'consumo' && $nivel_acceso != 'callcenter' && $nivel_acceso != 'administradorjr') { 
    header('Location: index.php'); 
    exit(); 
} 

include 'conexion.php'; 

$telefono = ''; 
$detalleCliente = []; 
$estatusMensaje = ''; 
$alertClass = ''; 
$promoMensaje = '';  // Asigna un valor vacío al inicio

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'search') { // Verificar acción
        if (isset($_POST['telefono'])) {
            $telefono = $_POST['telefono'];

            if (is_numeric($telefono) && strlen($telefono) === 8) {

                $sqlNDvalido = "exec mkt_prepago..sp_select_user_reintegro ?";
                $stmtNDvalido = sqlsrv_query($conn, $sqlNDvalido, [$telefono]);

                if ($stmtNDvalido && sqlsrv_fetch_array($stmtNDvalido, SQLSRV_FETCH_ASSOC)) {
                // Consulta SQL para verificar si el número tiene la promoción de regalo
                $sqlPromo = "exec mkt_prepago..sp_select_user_reintegro ?";
                $paramsPromo = array($telefono);
                $stmtPromo = sqlsrv_query($conn, $sqlPromo, $paramsPromo);
                if ($stmtPromo !== false && sqlsrv_fetch_array($stmtPromo, SQLSRV_FETCH_ASSOC)) {
                    // Si existe en la tabla de promociones
                    $sqlCliente = "exec mkt_prepago..sp_select_user_reintegro ?";
                    $paramsCliente = array($telefono);
                    $stmtCliente = sqlsrv_query($conn, $sqlCliente, $paramsCliente);

                    if ($stmtCliente !== false) {
                        $detalleCliente = sqlsrv_fetch_array($stmtCliente, SQLSRV_FETCH_ASSOC);
                        if ($detalleCliente === null) {
                            $detalleCliente = [];
                        }
                    } else {
                        $estatusMensaje = 'Error en la consulta.'; // Error en la consulta
                        $alertClass = 'alert-danger';
                    }
                } else {
                    // Si no existe en la tabla de promociones
                    $promoMensaje = "
                    <script>
                    alertify.alert('¡Importante!',
                    'El cliente aún tiene disponible el regalo navideño #VolvamosAConectarnos');
                    </script>";

                    $sqlCliente = "exec mkt_prepago..sp_select_user_reintegro ?";
                    $paramsCliente = array($telefono);
                    $stmtCliente = sqlsrv_query($conn, $sqlCliente, $paramsCliente);

                    if ($stmtCliente !== false) {
                        $detalleCliente = sqlsrv_fetch_array($stmtCliente, SQLSRV_FETCH_ASSOC);
                        if ($detalleCliente === null) {
                            $detalleCliente = [];
                        }
                    } else {
                        $estatusMensaje = 'Error en la consulta.'; // Error en la consulta
                        $alertClass = 'alert-danger';
                    }
                }
            } else {
                $promoMensaje = "
                <script>
                alertify.alert('Advertencia!',
                'El número no fue encontrado');
                </script>";
            }
        } else {
            $estatusMensaje = "
            <script>
            alertify.alert('Advertencia!',
            'El valor ingresado no es numérico o no posee 8 dígitos');
            </script>";
        }
    }
}
    
    // Bloquear o desbloquear número
    if (isset($_POST['accion_estado']) && isset($_POST['telefono'])) {
        $nuevoEstado = $_POST['accion_estado'] === 'BLOQUEAR' ? 'BLOQUEADO' : 'DESBLOQUEADO';
        $sqlUpdate = "exec mkt_prepago..sp_update_status_blacklist ?, ?";
        $paramsUpdate = array($nuevoEstado, $_POST['telefono']);
        $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);

        if ($stmtUpdate !== false) {
            if ($nuevoEstado === 'DESBLOQUEADO') {
                $estatusMensaje = 'Número ' . htmlspecialchars($_POST['telefono']) . ' ha sido desbloqueado.';
                $alertClass = 'alert-success';
            } else {
                $estatusMensaje = 'Número ' . htmlspecialchars($_POST['telefono']) . ' ha sido bloqueado.';
                $alertClass = 'alert-warning';
            }
        } else {
            $estatusMensaje = 'Error al actualizar el estado del número.'; // Error en la actualización
            $alertClass = 'alert-danger';
        } 
    }
}

    // Crear nuevo usuario
    if (isset($_POST['action']) && $_POST['action'] === 'create_user') {
        $numero = $_POST['numero'];
        $estado = $_POST['estado'];

        // Verificar si el usuario ya existe
        $checkSql = "exec mkt_prepago..sp_select_user_reintegro ?";
        $checkParams = array($numero);
        $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);

        if ($checkStmt === false) {
            $estatusMensaje = 'Error al verificar el número existente.'; // Error en la verificación
            $alertClass = 'alert-danger';
        } elseif (sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
            $estatusMensaje = 'El número ' . $numero . ' ya está registrado'; // Número ya existe
            $alertClass = 'alert-warning';
        } else {
            $sql = "exec mkt_prepago..sp_insert_phone_blacklist ?, ?";
            $params = array($numero, $estado);
            $stmt = sqlsrv_query($conn, $sql, $params);
            if ($stmt === false) {
                $estatusMensaje = 'Error al crear el usuario.'; // Error en la creación
                $alertClass = 'alert-danger';
            } else {
                $estatusMensaje = 'Usuario registrado con éxito'; // Usuario creado
                $alertClass = 'alert-success';
            }
            sqlsrv_free_stmt($stmt); // Liberar el statement
        }
        sqlsrv_free_stmt($checkStmt); // Liberar el statement de verificación
    }

?> 

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="utf-8" /> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge" /> 
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" /> 
    <title>Consulta Reintegros</title> 
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" /> 
    <link href="css/styles.css" rel="stylesheet" /> 
    <link href="css/custom-styles.css" rel="stylesheet" /> 
        <!-- Estilos de AlertifyJS -->
    <link rel="stylesheet" href="assets/alertifyjs/css/alertify.min.css">
    <link rel="stylesheet" href="assets/alertifyjs/css/themes/default.min.css">
    <style> 
        .search-container { display: flex; justify-content: flex-end; align-items: center; gap: 10px; } 
        .search-container input { max-width: 300px; } 
    </style> 
   <script src="assets/alertifyjs/alertify.min.js"></script>
   <script>
    setTimeout(function() {
        alertify.alert('Aviso', 'Su sesión ha expirado.',
        function() {
            window.location.href = 'login.php';}
        );
    }, <?= $tiempoInactividad * 1000 ?>);
    </script>
</head> 

<body class="sb-nav-fixed"> 
    <?php include 'top-nav.php'; ?> 
    <div id="layoutSidenav"> 
        <?php include 'sidenav-menu.php'; ?> 
        <div id="layoutSidenav_content"> 
            <main> 
                <div class="container-fluid px-4"> 
                    <h1 class="mt-4"></h1> 
                    <!-- Formulario de Búsqueda --> 
                        <form method="POST" class="mb-4">
                            <div class="search-container">
                                <?php if ($estatusMensaje): ?>
                                    <div class="alert <?= $alertClass ?> alert-dismissible fade show w-100" role="alert">
                                        <?= $estatusMensaje ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                <input type="hidden" name="action" value="search"> <!-- Campo oculto para identificar la acción -->
                                <input type="text" name="telefono" class="form-control" placeholder="Ingrese el número de teléfono" required value="<?= htmlspecialchars($telefono) ?>">
                                <button type="submit" class="btn btn-success">
                                    <img src="img/buscar.png" alt="Ícono registrar" style="width: 24px; height: 24px; margin-right: 5px;"> Buscar
                                </button>
                            </div>
                        </form>
                    <?= $promoMensaje ?>
                    <div class="row"> 
                        <div class="col-md-12"> 
                            <div class="card mb-4"> 
                                <div class="card-header d-flex justify-content-between align-items-center">Información del Cliente                 
                                </div>
                                <div class="card-body"> 
                                    <?php if ($detalleCliente): ?> 
                                        <form method="POST"> 
                                            <input type="hidden" name="telefono" value="<?= htmlspecialchars($detalleCliente['TELEFONO']) ?>"> 
                                            <table class="table table-bordered"> 
                                                <thead> 
                                                    <tr> 
                                                        <th>Télefono</th> 
                                                        <th>Fecha Reintegro</th> 
                                                        <th>OTT</th>
                                                        <th>Descripción</th> 
                                                        <th>Monto Reintegro</th> 														
                                                    </tr> 
                                                </thead> 
                                                <tbody> 
                                                    <tr> 
                                                        <td><?= htmlspecialchars($detalleCliente['TELEFONO']) ?></td> 
                                                        <td>
                                                            <?php 
                                                            if (isset($detalleCliente['START_TIME'])) {
                                                                $fecha = $detalleCliente['START_TIME'];
                                                                if ($fecha instanceof DateTime) {
                                                                    echo $fecha->format('Y-m-d H:i:s');
                                                                } else {
                                                                    echo htmlspecialchars($detalleCliente['START_TIME']);
                                                                }
                                                            }
                                                            ?>
                                                        </td> 
                                                        <td><?= htmlspecialchars($detalleCliente['OTT']) ?></td> 
                                                        <td><?= htmlspecialchars($detalleCliente['OPERATOR_TYPE']) ?></td> 	
														<td>$<?= htmlspecialchars($detalleCliente['ACCOUNT_BALANCE_DELTA'], 2) ?></td> <!-- Agregado símbolo $ -->
                                                    </tr> 
                                                </tbody> 
                                            </table> 
                                        </form> 
                                    <?php else: ?> 
                                        <p>No se encontraron resultados.</p> 
                                    <?php endif; ?> 
                                </div> 
                            </div> 
                        </div> 
                    </div> 
                </div> 
            </main> 
            <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex justify-content-between align-items-center small">
                    <div class="text-muted">Mercadeo Prepago - Claro El Salvador</div>
                </div>
            </div>
            </footer>
        </div> 
    </div> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script> 
</body> 
</html>
