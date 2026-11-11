<?php
session_start();
require_once '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['role'])) {
    header("Location: ../views/login.php");
    exit();
}

$action = $_GET['action'] ?? '';

// Ambil ID user dari session dengan berbagai kemungkinan nama variabel agar tidak kosong
$user_id = $_SESSION['user_id'] ?? ($_SESSION['id'] ?? ($_SESSION['id_user'] ?? 0));

// Jika user_id masih 0 atau kosong, coba ambil berdasarkan email yang sedang login
if ($user_id == 0 && isset($_SESSION['email'])) {
    $stmt_u = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_u->execute([$_SESSION['email']]);
    $usr = $stmt_u->fetch(PDO::FETCH_ASSOC);
    if ($usr) {
        $user_id = $usr['id'];
        $_SESSION['user_id'] = $user_id; // Set ulang session agar permanen
    }
}

// Jika tetap tidak ditemukan, arahkan kembali ke login
if ($user_id == 0) {
    header("Location: ../views/login.php?error=" . urlencode("Sesi login bermasalah, silakan login ulang."));
    exit();
}

// ==========================================
// 1. PROSES TAMBAH KARYA (STORE)
// ==========================================
if ($action == 'store' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link_github = trim($_POST['link_github'] ?? '');
    $link_demo = trim($_POST['link_demo'] ?? '');
    
    // Upload Gambar
    $nama_file_gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed_ext)) {
            $nama_file_gambar = time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (!is_dir('../uploads')) {
                mkdir('../uploads', 0777, true);
            }
            move_uploaded_file($file_tmp, '../uploads/' . $nama_file_gambar);
        }
    }

    try {
        // Cek apakah tabel projects menggunakan kolom `user_id` atau `id_user`
        // Kita gunakan INSERT dengan mencoba kolom user_id terlebih dahulu
        $stmt = $pdo->prepare("INSERT INTO projects (nama_project, deskripsi, gambar, link_github, link_demo, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama_project, $deskripsi, $nama_file_gambar, $link_github, $link_demo, $user_id]);

        $redirect_page = ($_SESSION['role'] == 'siswa') ? 'dashboard_siswa.php' : (($_SESSION['role'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_sa.php');
        header("Location: ../views/" . $redirect_page . "?success=" . urlencode("Karya berhasil ditambahkan!"));
        exit();
    } catch (PDOException $e) {
        // Fallback jika kolomnya bernama id_user
        try {
            $stmt = $pdo->prepare("INSERT INTO projects (nama_project, deskripsi, gambar, link_github, link_demo, id_user) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama_project, $deskripsi, $nama_file_gambar, $link_github, $link_demo, $user_id]);

            $redirect_page = ($_SESSION['role'] == 'siswa') ? 'dashboard_siswa.php' : (($_SESSION['role'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_sa.php');
            header("Location: ../views/" . $redirect_page . "?success=" . urlencode("Karya berhasil ditambahkan!"));
            exit();
        } catch (PDOException $ex) {
            header("Location: ../views/project.php?action=tambah&error=" . urlencode("Gagal menyimpan ke database: " . $ex->getMessage()));
            exit();
        }
    }
}

// ==========================================
// 2. PROSES UPDATE / EDIT KARYA
// ==========================================
if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_project = $_POST['id'] ?? 0;
    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link_github = trim($_POST['link_github'] ?? '');
    $link_demo = trim($_POST['link_demo'] ?? '');

    try {
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed_ext)) {
                $nama_file_gambar = time() . '_' . rand(1000, 9999) . '.' . $ext;
                move_uploaded_file($file_tmp, '../uploads/' . $nama_file_gambar);

                $stmt = $pdo->prepare("UPDATE projects SET nama_project = ?, deskripsi = ?, gambar = ?, link_github = ?, link_demo = ? WHERE id = ?");
                $stmt->execute([$nama_project, $deskripsi, $nama_file_gambar, $link_github, $link_demo, $id_project]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE projects SET nama_project = ?, deskripsi = ?, link_github = ?, link_demo = ? WHERE id = ?");
            $stmt->execute([$nama_project, $deskripsi, $link_github, $link_demo, $id_project]);
        }

        $redirect_page = ($_SESSION['role'] == 'siswa') ? 'dashboard_siswa.php' : (($_SESSION['role'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_sa.php');
        header("Location: ../views/" . $redirect_page . "?success=" . urlencode("Karya berhasil diperbarui!"));
        exit();
    } catch (PDOException $e) {
        header("Location: ../views/project.php?action=edit&id=" . $id_project . "&error=" . urlencode("Gagal memperbarui data: " . $e->getMessage()));
        exit();
    }
}

// ==========================================
// 3. PROSES HAPUS KARYA
// ==========================================
if ($action == 'hapus' && isset($_GET['id'])) {
    $id_project = $_GET['id'];

    try {
        $stmt_img = $pdo->prepare("SELECT gambar FROM projects WHERE id = ?");
        $stmt_img->execute([$id_project]);
        $proj = $stmt_img->fetch(PDO::FETCH_ASSOC);

        if ($proj && !empty($proj['gambar']) && file_exists('../uploads/' . $proj['gambar'])) {
            unlink('../uploads/' . $proj['gambar']);
        }

        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id_project]);

        $redirect_page = ($_SESSION['role'] == 'siswa') ? 'dashboard_siswa.php' : (($_SESSION['role'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_sa.php');
        header("Location: ../views/" . $redirect_page . "?success=" . urlencode("Karya berhasil dihapus."));
        exit();
    } catch (PDOException $e) {
        $redirect_page = ($_SESSION['role'] == 'siswa') ? 'dashboard_siswa.php' : (($_SESSION['role'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_sa.php');
        header("Location: ../views/" . $redirect_page . "?error=" . urlencode("Gagal menghapus karya."));
        exit();
    }
}
?>