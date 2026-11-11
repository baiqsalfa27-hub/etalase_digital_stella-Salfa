<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'super_admin') header("Location: dashboard_sa.php");
    elseif ($_SESSION['role'] === 'admin') header("Location: dashboard_admin.php");
    elseif ($_SESSION['role'] === 'siswa') header("Location: dashboard_siswa.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <img src="../img/logo-ts2.png" alt="Logo" class="auth-logo" onerror="this.style.display='none'">
            <h2>Etalase Digital Stella</h2>
            <p>SMK Telkom Lampung</p>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="auth-alert-error"><?= htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div class="auth-alert-success"><?= htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>

            <form action="../controllers/authController.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group-auth">
                    <label>Email Sekolah</label>
                    <input type="email" name="email" class="form-control-auth" placeholder="nama@stella.sch.id" required>
                </div>
                <div class="form-group-auth">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control-auth" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-auth-submit">Login</button>
            </form>
            
            <div class="auth-footer-text">
                Belum punya akun? <a href="register.php">Daftar Siswa</a>
            </div>
        </div>
    </div>
    <footer>© 2026 Etalase Sekolah Digital • Baiq Salva Sheyna Arfani</footer>
</body>
</html>