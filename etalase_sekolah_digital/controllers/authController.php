<?php
session_start();
require_once '../config/database.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        header("Location: ../views/login.php?error=" . urlencode("Email dan password wajib diisi!"));
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifikasi password (mendukung hash maupun teks biasa untuk cadangan)
        if ($user && (password_verify($password, $user['password'] ?? '') || $password === $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            
            // Normalisasi role agar aman dari perbedaan huruf besar/kecil atau spasi
            $role = strtolower(trim($user['role'] ?? 'siswa'));
            if ($role === 'super admin' || $role === 'super_admin') {
                $role = 'superadmin';
            }
            $_SESSION['role'] = $role;

            // Redirect berdasarkan role masing-masing
            if ($role === 'superadmin') {
                header("Location: ../views/dashboard_sa.php");
                exit();
            } elseif ($role === 'admin') {
                header("Location: ../views/dashboard_admin.php");
                exit();
            } else {
                header("Location: ../views/dashboard_siswa.php");
                exit();
            }
        } else {
            header("Location: ../views/login.php?error=" . urlencode("Email atau password salah!"));
            exit();
        }
    } catch (Exception $e) {
        header("Location: ../views/login.php?error=" . urlencode("Terjadi kesalahan sistem: " . $e->getMessage()));
        exit();
    }
} elseif ($action === 'logout') {
    session_unset();
    session_destroy();
    header("Location: ../views/login.php");
    exit();
} else {
    header("Location: ../views/login.php");
    exit();
}