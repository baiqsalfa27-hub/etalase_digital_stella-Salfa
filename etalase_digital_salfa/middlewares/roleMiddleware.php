<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkRole($allowed_roles = []) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../views/login.php");
        exit();
    }
}
?>