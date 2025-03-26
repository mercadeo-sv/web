<?php

/* Válida el tiempo de sesión límite*/
include 'politicas_sesion.php';

if ($nivel_acceso != 'administrador' && $nivel_acceso != 'consumo' && $nivel_acceso != 'callcenter' && $nivel_acceso != 'administradorjr') {
    header('Location: index.php');
    exit();
}

include 'conexion.php'; // Incluir el archivo de conexión

$telefono = ''; // Aloja el teléfono a consultar
$detalleCliente = []; // Variable para almacenar detalles del cliente
$detalleRecargas = []; // Variable para almacenar detalles de recargas
$detalleConsumo = []; // Variable para almacenar detalles de paquetes
$detalleSVA = []; // Variable para almacenar detalles de SVA
$detalleCAV = []; // Variable para almacenar detalles de CAV
$detalleCTP = []; // Variable para almacenar detalles de CTP
$estatusMensaje = ''; // Aloja el estatus del cliente
$promoMensaje = ''; // Aloja el mensaje de promoción

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = $_POST['telefono'];

    // Validación del lado del servidor
    if (is_numeric($telefono) && strlen($telefono) === 8) {
        $sqlNDvalido = "exec mkt_prepago..sp_select_detail_user ?";
        $stmtNDvalido = sqlsrv_query($conn, $sqlNDvalido, [$telefono]);

        if ($stmtNDvalido && sqlsrv_fetch_array($stmtNDvalido, SQLSRV_FETCH_ASSOC)) {
            // Verificar promoción
            $sqlPromo = "exec mkt_prepago..sp_select_detail_user ?";
            $stmtPromo = sqlsrv_query($conn, $sqlPromo, [$telefono]);
            $promoDisponible = $stmtPromo && sqlsrv_fetch_array($stmtPromo, SQLSRV_FETCH_ASSOC);

            if (!$promoDisponible) {
                $promoMensaje = "
                <script>
                alertify.alert('Importante!',
                'El cliente aún tiene disponible el regalo navideño #VolvamosAConectarnos');
                </script>";
            }

            // Obtener y procesar detalles
            $detalleCliente = obtenerDetalleCliente($conn, $telefono);
            $estatusMensaje = determinarEstatus($detalleCliente);

            $detalleRecargas = obtenerDetalles($conn, $telefono, 'sp_select_recharge_user');
            $detalleConsumo = obtenerDetalles($conn, $telefono, 'sp_select_packs_user');
            $detalleCAV = obtenerDetalles($conn, $telefono, 'sp_select_cav_user');
            $detalleCTP = obtenerDetalles($conn, $telefono, 'sp_select_ctp_user');
            $detalleSVA = obtenerDetalles($conn, $telefono, 'sp_select_sva_user');
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

// Función para obtener el detalle del cliente
function obtenerDetalleCliente($conn, $telefono) {
    $sql = "exec mkt_prepago..sp_select_detail_user ?";
    $stmt = sqlsrv_query($conn, $sql, [$telefono]);
    return $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;
}

// Función para determinar el estatus
function determinarEstatus($detalleCliente) {
    if (!$detalleCliente) return null;

    $estatus = $detalleCliente['ESTATUS'] ?? null;
    $mensajes = [
        1 => '<span class="badge bg-danger">FTU</span>',
        2 => '<span class="badge bg-success">Activo</span>',
        3 => '<span class="badge bg-warning text-dark">Pasivo</span>',
        4 => '<span class="badge bg-warning text-dark">Expirado</span>',
        5 => '<span class="badge bg-danger">Desactivado</span>',
    ];

    return $mensajes[$estatus] ?? '';
}

// Función genérica para obtener detalles de una consulta SQL
function obtenerDetalles($conn, $telefono, $procedimiento) {
    $sql = "exec mkt_prepago..$procedimiento ?";
    $stmt = sqlsrv_query($conn, $sql, [$telefono]);
    $detalles = [];

    while ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $detalles[] = $row;
    }

    return $detalles;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Consulta Cliente</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
        <!-- Archivo CSS adicional -->
    <link href="css/custom-styles.css" rel="stylesheet" /> 
    <link href="css/styles-alertify.css" rel="stylesheet" /> 
        <!-- Estilos de AlertifyJS -->
    <link rel="stylesheet" href="assets/alertifyjs/css/alertify.min.css">
    <link rel="stylesheet" href="assets/alertifyjs/css/themes/default.min.css">
    <style>
        .search-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
        }
        .search-container input {
            max-width: 300px;
        }
        /* Estilos para las pestañas */
        .nav-pills .nav-link {
            color: #6c757d; /* Gris oscuro para el texto de las pestañas */
        }
        .nav-pills .nav-link.active {
            color: #fff; /* Blanco para el texto en la pestaña activa */
            background-color: #343a40; /* Gris oscuro para el fondo de la pestaña activa */
        }
        .nav-pills .nav-link.disabled {
            color: #6c757d; /* Gris oscuro para el texto en la pestaña deshabilitada */
        }
        /* Estilos para los círculos de 'Si' y 'No' */
        .circle {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            color: #fff;
            font-weight: bold;
        }
        .circle-yes {
            background-color: #28a745; /* Verde para 'Si' */
        }
        .circle-no {
            background-color: #dc3545; /* Rojo para 'No' */
        }
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
                                <div class="me-16">
                                    <?= $estatusMensaje ?>
                                </div>
                            <?php endif; ?>
                            <input type="text" name="telefono" class="form-control" placeholder="Ingrese el número de teléfono" required value="<?= htmlspecialchars($telefono) ?>">
                            <button type="submit" class="btn btn-success">
                            <img src="img/buscar.png" alt="Ícono registrar" style="width: 24px; height: 24px; margin-right: 5px;">
                            Buscar
                            </button> <!-- Botón en verde a la derecha -->
                        </div>
                    </form>
                    <?= $promoMensaje ?>
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card mb-4">
                                <div class="card-header">Detalles del Cliente</div>
                                <div class="card-body">
                                    <?php if ($detalleCliente): ?>
                                        <table class="table table-bordered">
                                            <tr><th>Teléfono</th><td><?= $detalleCliente['TELEFONO'] ?></td></tr>
                                            <tr><th>Nombre</th><td><?= $detalleCliente['NOMBRE_COMPLETO'] ?></td></tr>
                                            <tr><th>Documento</th><td><?= $detalleCliente['DOCUMENTO'] ?></td></tr>
                                            <tr><th>Fecha de Nacimiento</th><td><?= $detalleCliente['FECHA_NACIMIENTO']->format('d M Y') ?></td></tr>
                                            <tr><th>Dispositivo</th><td><?= $detalleCliente['GSMA_BRAND_NAME'] . ' / ' . $detalleCliente['GSMA_MARKETING_NAME']?></td></tr>
                                        </table>
                                    <?php else: ?>
                                        <p>No se encontraron detalles para el número ingresado.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card mb-4">
                                <div class="card-header">Detalles del Número</div>
                                <div class="card-body">
                                    <?php if ($detalleCliente): ?>
                                        <table class="table table-bordered">
                                            <tr><th>Fecha de Activación</th><td><?= $detalleCliente['FECHA_ACTIVACION']->format('Y-m-d') ?></td></tr>
                                            <tr><th>Antigüedad</th><td><?= $detalleCliente['ANTIGUEDAD'] ?></td></tr>
                                            <tr><th>Perfil Oferta</th><td><?= $detalleCliente['IDPERFIL'] . ' / ' . $detalleCliente['TEXTO']?></td></tr>
                                            <tr><th>Cuenta Principal</th><td>$<?=$detalleCliente['CTA_PRINCIPAL'] ?></td></tr>
                                            <tr><th>Perfil Eléctrico / SIM</th><td><?= $detalleCliente['SMP_VERSION'] . ' / ' . $detalleCliente['MAXIMA_TECNOLOGIA_SIM'] ?></td></tr>
                                        </table>
                                    <?php else: ?>
                                        <p>No se encontraron detalles para el número ingresado.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de Recargas, Consumo, Agregadores, CAV y CTP en formato de pestañas -->
                    <ul class="nav nav-pills card-header-pills" id="myTab" role="tablist">
                        <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'consumo'): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= ($nivel_acceso != 'callcenter' && $nivel_acceso != 'administradorjr') ? 'active' : '' ?>" id="recargas-tab" data-bs-toggle="tab" data-bs-target="#recargas" type="button" role="tab" aria-controls="recargas" aria-selected="true">Recargas</button> <!-- Pestaña en gris oscuro -->
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="consumo-tab" data-bs-toggle="tab" data-bs-target="#consumo" type="button" role="tab" aria-controls="consumo" aria-selected="false">Consumos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="cav-tab" data-bs-toggle="tab" data-bs-target="#cav" type="button" role="tab" aria-controls="cav" aria-selected="false">Alto Valor</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="sva-tab" data-bs-toggle="tab" data-bs-target="#sva" type="button" role="tab" aria-controls="sva" aria-selected="false">Agregadores</button>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= ($nivel_acceso == 'callcenter' || $nivel_acceso == 'administradorjr') ? 'active' : '' ?>" id="ctp-tab" data-bs-toggle="tab" data-bs-target="#ctp" type="button" role="tab" aria-controls="ctp" aria-selected="true">Claro Te Presta</button> <!-- Nueva pestaña -->
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="myTabContent">
                        <?php if ($nivel_acceso == 'administrador' || $nivel_acceso == 'consumo'): ?>
                            <div class="tab-pane fade <?= ($nivel_acceso != 'callcenter' && $nivel_acceso != 'administradorjr') ? 'show active' : '' ?>" id="recargas" role="tabpanel" aria-labelledby="recargas-tab">
                            <?php if ($detalleRecargas): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Zona</th>
                                            <th>Departamento</th>
                                            <th>Canal</th>
                                            <th>Distribuidor</th>
                                            <th>Paquete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalleRecargas as $recarga): ?>
                                            <tr>
                                                <td><?= $recarga['FECHAHORA']->format('Y-m-d H:i:s') ?></td>
                                                <td><?= $recarga['ZONA'] ?></td>
                                                <td><?= $recarga['DEPARTAMENTO'] ?></td>
                                                <td><?= $recarga['CANAL'] ?></td>
                                                <td><?= $recarga['DISTRIBUIDOR'] ?></td>
                                                <td><?= $recarga['PAQUETE'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No se encontraron recargas para el número ingresado.</p>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade" id="consumo" role="tabpanel" aria-labelledby="consumo-tab">
                            <?php if ($detalleConsumo): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Zona</th>
                                            <th>Departamento</th>
                                            <th>Canal</th>
                                            <th>Distribuidor</th>
                                            <th>Paquete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalleConsumo as $consumo): ?>
                                            <tr>
                                                <td><?= $consumo['FECHAHORA']->format('Y-m-d H:i:s') ?></td>
                                                <td><?= $consumo['ZONA'] ?></td>
                                                <td><?= $consumo['DEPARTAMENTO'] ?></td>
                                                <td><?= $consumo['CANAL'] ?></td>
                                                <td><?= $consumo['DISTRIBUIDOR'] ?></td>
                                                <td><?= $consumo['PAQUETE'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No se encontraron registros de consumo para el número ingresado.</p>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade" id="cav" role="tabpanel" aria-labelledby="cav-tab">
                            <?php if ($detalleCAV): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Periodo</th>
                                            <th>Segmento Antigüedad</th>
                                            <th>Segmento Arpu</th>
                                            <th>Arpu</th>
                                            <th>Canje</th>
                                            <th>Fecha de Canje</th>
                                            <th>Paquete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalleCAV as $cav): ?>
                                            <tr>
                                                <td><?= $cav['IDPERIODO'] ?></td>
                                                <td><?= $cav['SEGMENTO_ANTIGUEDAD'] ?></td>
                                                <td><?= $cav['SEGMENTO_VALOR'] ?></td>
                                                <td>$<?= number_format($cav['ARPU'], 2) ?></td> <!-- Agregado símbolo $ -->
                                                <td>
                                                    <span class="circle <?= $cav['CANJE'] === 'SI' ? 'circle-yes' : 'circle-no' ?>">
                                                        <?= $cav['CANJE'] ?>
                                                    </span>
                                                </td>
                                                <td><?= $cav['FECHA_CANJE']->format('Y-m-d H:i:s') ?></td>
                                                <td><?= $cav['PAQUETE'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>Cliente no aplica a segmento CAV.</p>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade" id="sva" role="tabpanel" aria-labelledby="sva-tab">
                            <?php if ($detalleSVA): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Zona</th>
                                            <th>Departamento</th>
                                            <th>Canal</th>
                                            <th>Agregador</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalleSVA as $sva): ?>
                                            <tr>
                                                <td><?= $sva['FECHAHORA']->format('Y-m-d H:i:s') ?></td>
                                                <td><?= $sva['ZONA'] ?></td>
                                                <td><?= $sva['DEPARTAMENTO'] ?></td>
                                                <td><?= $sva['CANAL'] ?></td>
                                                <td><?= $sva['AGREGADOR'] ?></td>
                                                <td>$<?= number_format($sva['SIN_IVA'], 2) ?></td> <!-- Agregado símbolo $ -->
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No se encontraron suscripciones de agregadores.</p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <div class="tab-pane fade <?= ($nivel_acceso == 'callcenter' || $nivel_acceso == 'administradorjr') ? 'show active' : '' ?>" id="ctp" role="tabpanel" aria-labelledby="ctp-tab">
                            <?php if ($detalleCTP): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Paquete</th>
                                            <th>Segmento</th>
                                            <th>Último Pago</th>
                                            <th>Deuda Actual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalleCTP as $ctp): ?>
                                            <tr>
                                                <td><?= $ctp['FECHAHORA']->format('Y-m-d H:i:s') ?></td>
                                                <td><?= $ctp['CATEGORIADE'] ?></td>
                                                <td><?= $ctp['SEGMENTO'] ?></td>
                                                <td><?= $ctp['FECHA_ULT_PAGO'] ?></td>
                                                <td>$<?= number_format($ctp['DEUDA_ACTUAL'], 2)?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No se encontraron registros de préstamos para el número ingresado.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>