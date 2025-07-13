<?php
require_once '../includes/functions.php';

// Destruir sesión
$sceneiq->logoutUser();

// Redirigir al inicio
header('Location: ../index.php');
exit;
?>