<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['usuario_id']);
session_destroy();

header("Location: login.php");
exit;
?>