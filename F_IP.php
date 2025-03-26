<?php
function getClientIP() {
    $ipAddress = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }

    // Verificar si es una dirección IPv6 de loopback
    if ($ipAddress == '::1') {
        $ipAddress = '127.0.0.1';
    }

    return $ipAddress;
}

// Ejemplo de uso
$clientIP = getClientIP();
?>
<!DOCTYPE html>
<html lang="en">
    <div style="display: flex; align-items: center;">
        <img src="img/logo_ip.png" alt="Icono de IP" style="width: 24px; height: 24px; margin-right: 8px;">
        <span><?php echo $clientIP; ?></span>
    </div>
</html>


