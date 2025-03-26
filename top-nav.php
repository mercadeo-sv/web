<?php
if (!isset($_SESSION['nombre_usuario'])) {
    header('Location: login.php');
    exit();
}
$nombre_usuario = $_SESSION['nombre_usuario'];
?>
 
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand -->
    <a class="navbar-brand ps-3" href="index.php">
        <img src="logo_peq_claro.png" style="width: 25px; height: 25px;">
        Mercadeo Prepago
    </a>
 
    <!-- User Menu -->
    <div class="ms-auto d-flex align-items-center">
        <!-- User Dropdown Menu -->
        <div class="dropdown">
            <a class="nav-link dropdown-toggle user-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span><?= htmlspecialchars($nombre_usuario) ?></span>
                <img src="img/cerrar-sesion.png" alt="Usuario" style="width: 25px; height: 25px; margin-left: 5px;">
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="cambio_contraseña.php">Cambiar contraseña</a></li>
                <li><a class="dropdown-item" href="logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>
 
<style>
    .user-link, .user-link span {
        color: #ffffff;
    }
 
    .user-link:hover {
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.7);
    }
 
    .dropdown-menu {
        background-color: #343a40;
        border-color: #454d55;
    }
 
    .dropdown-item {
        color: #ffffff;
    }
 
    .dropdown-item:hover {
        background-color: #ffffff;
        color: #6c757d; /* Gris */
    }
</style>