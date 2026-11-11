<?php
session_start();
require_once '../config/database.php';

// Pastikan hanya Super Admin yang bisa menghapus
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../views/login.php?error=" . urlencode("Akses ditolak."));
    exit();
}

// Cek apakah ada parameter ID yang dikirim
if (isset($_GET['id'])) {
    $id_user = $_GET['id'];

    // Mencegah Super Admin menghapus akunnya sendiri yang sedang login
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id_user) {
        header("Location: ../views/users.php?error=" . urlencode("Kamu tidak dapat menghapus akunmu sendiri yang sedang aktif!"));
        exit();
    }

    try {
        // Cek apakah database menggunakan kolom 'users' atau 'id'
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id_user]);

        // Redirect kembali ke halaman kelola user dengan pesan sukses
        header("Location: ../views/users.php?success=" . urlencode("User berhasil dihapus dari sistem."));
        exit();

    } catch (PDOException $e) {
        // Jika gagal karena ada relasi data (Foreign Key constraint), tangkap errornya agar tidak layar putih
        header("Location: ../views/users.php?error=" . urlencode("Gagal menghapus user: User ini masih memiliki data terkait di sistem."));
        exit();
    }
} else {
    header("Location: ../views/users.php");
    exit();
}
?>