<?php
session_start();
require_once '../config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email sudah terdaftar. Silakan login.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'siswa')");
            if ($stmt->execute([$nama, $email, $hashedPassword])) {
                header("Location: login.php?success=" . urlencode("Registrasi berhasil! Silakan login."));
                exit();
            } else {
                $error = "Terjadi kesalahan saat mendaftar.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Siswa - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <img src="../img/logo-ts2.png" alt="Logo" class="auth-logo" onerror="this.style.display='none'">
            <h2>Daftar Akun Siswa</h2>
            <p>SMK Telkom Lampung</p>
            
            <?php if (!empty($error)): ?>
                <div class="auth-alert-error"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group-auth">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control-auth" placeholder="Baiq Salva Sheyna Arfani" required>
                </div>
                <div class="form-group-auth">
                    <label>Email Sekolah</label>
                    <input type="email" name="email" class="form-control-auth" placeholder="nama@stella.sch.id" required>
                </div>
                <div class="form-group-auth">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control-auth" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-auth-submit">Daftar Sekarang</button>
            </form>
            
            <div class="auth-footer-text">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </div>
    </div>
    <footer>© 2026 Etalase Digital Stella • Baiq Salva Sheyna Arfani</footer>
</body>
</html>