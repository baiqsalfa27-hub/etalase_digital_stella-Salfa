<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['role'])) {
    header("Location: login.php?error=" . urlencode("Silakan login terlebih dahulu."));
    exit();
}

$action = $_GET['action'] ?? 'tambah';$id_user = $_SESSION['user_id'] ?? ($_SESSION['id'] ?? 0);
$nama_user =$_SESSION['nama'] ?? 'User';
$role_user =$_SESSION['role'];

$error = '';
$success = '';$project = [
    'nama_project' => '',
    'deskripsi' => '',
    'link_github' => '',
    'link_demo' => '',
    'gambar' => ''
];

// Jika mode edit, ambil data lama
if ($action === 'edit' && isset($_GET['id'])) {
    $id_proj =$_GET['id'];
    $stmt =$pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id_proj]);
    $project =$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$project) {
        header("Location: dashboard_sa.php?error=" . urlencode("Data karya tidak ditemukan."));
        exit();
    }
}

// Proses form saat disubmit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = trim($_POST['nama_project'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $link_github = trim($_POST['link_github'] ?? '');
    $link_demo = trim($_POST['link_demo'] ?? '');
    $gambar_lama =$_POST['gambar_lama'] ?? '';

    if (empty($nama_project) || empty($deskripsi)) {$error = "Nama project dan deskripsi wajib diisi!";
    } else {
        // Upload gambar jika ada
        $nama_file_gambar =$gambar_lama;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {$file_tmp = $_FILES['gambar']['tmp_name'];$file_name = $_FILES['gambar']['name'];$ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));$allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed_ext)) {$nama_file_gambar = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);$upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                move_uploaded_file($file_tmp, $upload_dir .$nama_file_gambar);
            }
        }

        try {
            if ($action === 'tambah') {
                // INSERT data baru, sertakan id_user / user_id agar kepemilikan tercatat!
                $stmtIns =$pdo->prepare("INSERT INTO projects (user_id, nama_project, deskripsi, gambar, link_github, link_demo, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmtIns->execute([$id_user,$nama_project, $deskripsi,$nama_file_gambar, $link_github,$link_demo]);
                
                // Redirect sesuai role
                $target_dashboard = ($role_user === 'super_admin' || $role_user === 'superadmin') ? 'dashboard_sa.php' : ($role_user === 'admin' ? 'dashboard_admin.php' : 'dashboard_siswa.php');
                header("Location: " . $target_dashboard . "?success=" . urlencode("Karya berhasil diunggah!"));
                exit();
            } else {
                // UPDATE data
                $id_proj =$_GET['id'];
                $stmtUpd =$pdo->prepare("UPDATE projects SET nama_project = ?, deskripsi = ?, gambar = ?, link_github = ?, link_demo = ? WHERE id = ?");
                $stmtUpd->execute([$nama_project,$deskripsi, $nama_file_gambar,$link_github, $link_demo,$id_proj]);

                $target_dashboard = ($role_user === 'super_admin' || $role_user === 'superadmin') ? 'dashboard_sa.php' : ($role_user === 'admin' ? 'dashboard_admin.php' : 'dashboard_siswa.php');
                header("Location: " . $target_dashboard . "?success=" . urlencode("Karya berhasil diperbarui!"));
                exit();
            }
        } catch (Exception $e) {$error = "Gagal menyimpan ke database: " . $e->getMessage();
        }
    }
}

// Tentukan arah kembali berdasarkan role
$dashboard_link = ($role_user === 'super_admin' || $role_user === 'superadmin') ? 'dashboard_sa.php' : ($role_user === 'admin' ? 'dashboard_admin.php' : 'dashboard_siswa.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($action); ?> Karya - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/dashboard_sa.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="<?= $dashboard_link; ?>" class="brand">
                <img src="../img/logo-ts2.png" alt="Logo" onerror="this.style.display='none'">
                <span>Etalase Digital Stella</span>
            </a>
        </div>
        <div class="nav-links">
            <a href="<?= $dashboard_link; ?>" class="nav-item">Kembali ke Dashboard</a>
            <div class="user-profile-box" style="border-left: 1px solid #e5e7eb; padding-left: 1rem;">
                <span class="user-name"><?= htmlspecialchars($nama_user); ?></span>
                <span class="user-role"><?= strtoupper($role_user); ?></span>
            </div>
            <a href="../controllers/authController.php?action=logout" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="sa-container" style="max-width: 700px; margin: 2.5rem auto; padding: 0 1.5rem;">
        <div style="background: white; padding: 2.5rem; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; margin-top: 0;"><?= ucfirst($action); ?> Karya Portofolio</h2>
            <p style="font-size: 0.9rem; color: #6b7280; margin-bottom: 2rem;">Lengkapi informasi detail karya atau aplikasi yang ingin kamu tampilkan di etalase digital sekolah.</p>

            <?php if (!empty($error)): ?>
                <div style="background: #fef2f2; color: #dc2626; padding: 0.9rem; border-radius: 10px; border: 1px solid #fecaca; margin-bottom: 1.5rem; font-weight: 600; font-size: 0.9rem;">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($project['gambar']); ?>">

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Nama Project / Karya</label>
                    <input type="text" name="nama_project" value="<?= htmlspecialchars($project['nama_project']); ?>" placeholder="Contoh: Aplikasi Kasir Cafe Native PHP" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; outline: none;">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Deskripsi Singkat Karya</label>
                    <textarea name="deskripsi" rows="4" placeholder="Jelaskan fitur, teknologi (PHP, MySQL, Bootstrap), dan kegunaan project ini..." required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; outline: none; font-family: inherit;"><?= htmlspecialchars($project['deskripsi']); ?></textarea>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Upload Gambar Preview (Screenshot)</label>
                    <input type="file" name="gambar" accept="image/*" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.85rem; background: #f9fafb; box-sizing: border-box;">
                    <?php if (!empty($project['gambar'])): ?>
                        <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.4rem;">Gambar saat ini: <b><?= htmlspecialchars($project['gambar']); ?></b></p>
                    <?php endif; ?>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Link Repository GitHub (Opsional)</label>
                    <input type="url" name="link_github" value="<?= htmlspecialchars($project['link_github']); ?>" placeholder="https://github.com/username/namarepo" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; outline: none;">
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;">Link Live Demo (Opsional)</label>
                    <input type="url" name="link_demo" value="<?= htmlspecialchars($project['link_demo']); ?>" placeholder="https://namaproject.vercel.app" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; outline: none;">
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" style="flex: 1; background: #800020; color: white; padding: 0.75rem; border-radius: 8px; border: none; font-weight: 700; font-size: 0.95rem; cursor: pointer;">Simpan Karya</button>
                    <a href="<?= $dashboard_link; ?>" style="flex: 1; background: #e5e7eb; color: #374151; text-align: center; padding: 0.75rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; justify-content: center;">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <footer>© 2026 Etalase Digital Stella • Baiq Salva Sheyna Arfani</footer>
</body>
</html>