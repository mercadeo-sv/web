<?php


// Enviar correo con existencias críticas
$to = "erick.hernandez@claro.com.sv";
$subject = "Existencias Críticas";
$message = "Las siguientes existencias están en nivel crítico:\n";
$headers = "From: erick.hernandez@claro.com.sv";
mail($to, $subject, $message, $headers);

echo "Correo enviado.";
?>
