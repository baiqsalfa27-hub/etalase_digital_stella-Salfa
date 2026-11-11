<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'super_admin') {
        header("Location: views/dashboard_sa.php");
    } elseif ($_SESSION['role'] == 'admin') {
        header("Location: views/dashboard_admin.php");
    } else {
        header("Location: views/dashboard_siswa.php");
    }
} else {
    header("Location: views/login.php");
}
exit();
?>

<?php
header("Location: views/login.php");
exit();
?>