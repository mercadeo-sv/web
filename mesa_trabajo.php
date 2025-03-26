<?php

/* Política de sesion diferente a las demás páginas */
session_start();
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}

// Tiempo de inactividad en segundos
$tiempoInactividad = 60000; // 600 = 10 Minutos con sesión activa

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
/* Termina Script de política de seguridad*/

include 'conexion.php'; // Conexión a la base de datos

// Establecer la zona horaria a la deseada
date_default_timezone_set('America/El_Salvador'); // Cambia 'America/El_Salvador' a tu zona horaria si es diferente

// Obtener el perfil del usuario desde la base de datos
$usuario_actual = $_SESSION['nombre_usuario']; // Se asume que el nombre de usuario está en la sesión
$sql_perfil = "SELECT perfil FROM MKT_PREPAGO.dbo.TBL_TAREAS_USUARIOS WHERE nombre_usuario = ?";
$params_perfil = [$usuario_actual];
$stmt_perfil = sqlsrv_query($conn, $sql_perfil, $params_perfil);

// Verificar si la consulta fue exitosa
if ($stmt_perfil === false) {
    echo "Error en la consulta de perfil: ";
    print_r(sqlsrv_errors()); // Muestra el error SQL
    exit; // Detiene la ejecución del script si ocurre un error en la consulta
}

$row_perfil = sqlsrv_fetch_array($stmt_perfil, SQLSRV_FETCH_ASSOC);

// Verificar si se obtuvo algún resultado
if ($row_perfil === false) {
    echo "No se encontró el perfil para el usuario: $usuario_actual";
    exit; // Detiene la ejecución si no se encuentra el perfil
}

$perfil = $row_perfil['perfil'] ?? '';


// Obtener lista de usuarios
function obtenerUsuarios($conn) {
    $sql = "exec mkt_prepago.dbo.sp_obtenerusuario";
    $stmt = sqlsrv_query($conn, $sql);
    $usuarios = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $usuarios[] = $row['nombre_usuario'];
    }
    return $usuarios;
}
$usuarios = obtenerUsuarios($conn);

// Procesar nuevas tareas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'addTask') {
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $usuario = $_POST['usuario'];
        $estado = 'todo';
        $fecha_entrega = $_POST['date'];
        
        // Incluir la fecha de entrega en la consulta SQL
        $sql = "INSERT INTO MKT_PREPAGO.dbo.TBL_TAREAS_TABLERO (fecha_hora, nombre_tarea, descripcion, usuario_asignado, estado, fecha_entrega) 
                VALUES (GETDATE(), ?, ?, ?, ?, ?)";
        $params = [$titulo, $descripcion, $usuario, $estado, $fecha_entrega];
        
        // Ejecutar la consulta SQL
        sqlsrv_query($conn, $sql, $params);
        exit;
    }
    
    
    if ($action === 'moveTask') {
        $id = $_POST['id'];
        $estado = $_POST['estado'];
        
        $sql = "UPDATE MKT_PREPAGO.dbo.TBL_TAREAS_TABLERO SET estado = ? WHERE id = ?";
        $params = [$estado, $id];
        sqlsrv_query($conn, $sql, $params);
        exit;
    }
    
    if ($action === 'removeTask') {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM MKT_PREPAGO.dbo.TBL_TAREAS_TABLERO WHERE id = ?";
        $params = [$id];
        sqlsrv_query($conn, $sql, $params);
        exit;
    }
}

function cargarTareas($conn, $estado) {
    // Verificar si la consulta depende de perfil o usuario actual
    global $perfil, $usuario_actual;
    if ($perfil === 'Sub-Gerente MKT') {
        $sql = "exec mkt_prepago.dbo.sp_vista_tareas_gerente ?";
        $params = [$estado];
    } else if ($perfil === 'Especialista MKT') {
        $sql = "exec mkt_prepago.dbo.sp_vista_tareas_especialista ?,?";
        $params = [$estado, $usuario_actual];
    } else {
        return;
    }

    $stmt = sqlsrv_query($conn, $sql, $params);

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Obtener la fecha de la tarea y la fecha actual, pero sin la hora
        $fecha_tarea = strtotime(date("Y-m-d", strtotime($row['fecha_hora']))); // Solo fecha (sin hora)
        $fecha_actual = strtotime(date("Y-m-d")); // Solo fecha (sin hora)
    
        // Definir el color de fondo de la fecha según la comparación de fechas
        if ($fecha_tarea > $fecha_actual) {
            $fecha_color = 'bg-success'; // Verde
            $texto_color = 'text-white'; // Blanco
        } elseif ($fecha_tarea == $fecha_actual) {
            $fecha_color = 'bg-warning'; // Amarillo
            $texto_color = 'text-dark'; // Oscuro para mejor contraste
        } else {
            $fecha_color = 'bg-danger'; // Rojo
            $texto_color = 'text-white'; // Blanco
        }
    
        echo "<div class='task' draggable='true' ondragstart='drag(event)' id='task-{$row['id']}'>";
		$fecha_tarea = $row['fecha_hora'] ?? '';

		if (!empty($fecha_tarea)) {
		echo "<small><span class='px-2 py-1 rounded $fecha_color $texto_color'>Fecha: {$fecha_tarea}</span></small><br><br>";
}
        echo "<h6>{$row['nombre_tarea']}</h6>";
        echo "<p>{$row['descripcion']}</p>";
        // Mostrar el botón Eliminar y el Nombre del usuario asignado solo si el usuario tiene perfil 'Sub-Gerente MKT'
        if ($perfil === 'Sub-Gerente MKT') {
            echo "<small>Asignado a: {$row['usuario_asignado']}</small><br>";
        }
        // Formatear la fecha sin la hora y aplicarle el color de fondo
        if ($perfil === 'Sub-Gerente MKT') {
            echo "<div class='text-center'>"; // Añadir contenedor para centrar
            echo "<button onclick='removeTask({$row['id']})' class='btn btn-danger btn-sm'>Eliminar</button>";
            echo "</div>"; // Cerrar el contenedor
        }
        echo "</div>";
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
    <title>Mesa de Trabajo</title>
    <link rel="shortcut icon" type="image/x-icon" href="logo_peq_claro.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Archivo CSS adicional -->
    <link href="css/custom-styles.css" rel="stylesheet" /> 
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .kanban-column { min-height: 300px; border: 1px solid #ccc; padding: 10px; }
        .task { background: #f8f9fa; padding: 10px; margin: 5px 0; cursor: grab; border: 1px solid #ddd; }
        /* Estilos personalizados para las columnas */
        .kanban-column.todo {
            background-color: #cceeff; /* Celeste claro */
        }
        .kanban-column.in-progress {
            background-color: #fff9c4; /* Amarillo claro */
        }
        .kanban-column.done {
            background-color: #b8e9b2; /* Verde claro */
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include 'top-nav.php'; ?>
    <div id="layoutSidenav">
        <?php include 'sidenav-menu.php'; ?>
        <div id="layoutSidenav_content">
            <main>
            <div class="container mt-4">
                <?php if ($perfil === 'Sub-Gerente MKT'): ?>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTaskModal">Agregar Tarea<img src="img/add-todo.png" alt="Icono de teléfono" style="width: 24px; height: 24px;"></button>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-4">
                        <h4>Por hacer</h4>
                        <div class="kanban-column todo" id="todo" ondrop="drop(event)" ondragover="allowDrop(event)">
                            <?php cargarTareas($conn, 'todo'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4>En progreso</h4>
                        <div class="kanban-column in-progress" id="in-progress" ondrop="drop(event)" ondragover="allowDrop(event)">
                            <?php cargarTareas($conn, 'in-progress'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4>Completado</h4>
                        <div class="kanban-column done" id="done" ondrop="drop(event)" ondragover="allowDrop(event)">
                            <?php cargarTareas($conn, 'done'); ?>
                        </div>
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

<!-- Modal para agregar tarea -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Nueva Tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="taskTitle" class="form-control mb-2" placeholder="Título" required>
                <textarea id="taskDesc" class="form-control mb-2" placeholder="Descripción" required></textarea>
                <select id="taskUser" class="form-control mb-2" required>
                    <option value="">Seleccione un usuario</option>
                    <?php foreach ($usuarios as $usuario) { echo "<option value='$usuario'>$usuario</option>"; } ?>
                </select>
                <label for="taskDate" class="form-label">Fecha Entrega</label>
                <input type="date" id="taskDate" class="form-control mb-2" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="addTask()">Agregar</button>
            </div>
        </div>
    </div>
</div>

    <script>
        function addTask() {
            let title = $('#taskTitle').val();
            let desc = $('#taskDesc').val();
            let user = $('#taskUser').val();
            let date = $('#taskDate').val();
            if (title.trim() === '' || user.trim() === '') return;
            $.post('', { action: 'addTask', titulo: title, descripcion: desc, usuario: user, date: date }, function(response) {
                location.reload();
            });
        }
        function allowDrop(event) {
            event.preventDefault();
        }
        function drag(event) {
            event.dataTransfer.setData("text", event.target.id);
        }
        function drop(event) {
            event.preventDefault();
            let taskId = event.dataTransfer.getData("text").replace("task-", "");
            let targetColumn = event.target.id;
            if (!targetColumn) return;
            $.post('', { action: 'moveTask', id: taskId, estado: targetColumn }, function(response) {
                location.reload();
            });
        }
        function removeTask(taskId) {
            $.post('', { action: 'removeTask', id: taskId }, function(response) {
                location.reload();
            });
        }
    </script>
</body>
</html>
