<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

// Pengecekan role Super Admin
if (!isset($_SESSION['role']) || (strtolower(trim($_SESSION['role'])) !== 'super_admin' && strtolower(trim($_SESSION['role'])) !== 'superadmin')) {
    header("Location: login.php?error=" . urlencode("Akses ditolak. Khusus Super Admin."));
    exit();
}

$nama_admin = $_SESSION['nama'] ?? 'Super Admin Stella';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah_user') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = trim($_POST['role'] ?? 'siswa');

    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = "Email sudah terdaftar di sistem.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmtIns = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmtIns->execute([$nama, $email, $hashedPassword, $role])) {
                    $success = "User baru berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan user.";
                }
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}
$total_users = count($users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/dashboard_sa.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="dashboard_sa.php" class="brand">
                <img src="../img/logo-ts2.png" alt="Logo" onerror="this.style.display='none'">
                <span>Etalase Digital Stella</span>
            </a>
        </div>
        <div class="nav-links">
            <a href="dashboard_sa.php" class="nav-item">Daftar Karya</a>
            <a href="user.php" class="nav-item active">Kelola User</a>
            <div class="user-profile-box" style="border-left: 1px solid #e5e7eb; padding-left: 1rem;">
                <span class="user-name"><?= htmlspecialchars($nama_admin); ?></span>
                <span class="user-role">SUPER ADMIN</span>
            </div>
            <a href="../controllers/authController.php?action=logout" class="btn-logout">Logout</a>
        </div>
    </nav>

    <!-- KONTEN UTAMA KELOLA USER -->
    <div class="sa-container" style="margin-top: 2rem;">
        
        <div class="sa-hero-banner" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 0.4rem; margin-top: 0;">Manajemen Pengguna 👥</h1>
                <p style="font-size: 0.95rem; opacity: 0.9; margin: 0;">Kelola daftar akun siswa dan staff admin yang terdaftar di dalam sistem.</p>
            </div>
            <div>
                <button type="button" onclick="document.getElementById('modalTambahUser').style.display='flex';" style="background: white; color: #800020; font-weight: 700; padding: 0.7rem 1.5rem; border-radius: 10px; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">+ Tambah User Baru</button>
            </div>
        </div>

        <?php if (!empty($success) || isset($_GET['success'])): ?>
            <div style="background: #f0fdf4; color: #16a34a; padding: 1rem; border-radius: 10px; border: 1px solid #bbf7d0; margin-bottom: 1.5rem; font-weight: 600;">
                <?= htmlspecialchars($success ?: $_GET['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div style="background: #fef2f2; color: #dc2626; padding: 1rem; border-radius: 10px; border: 1px solid #fecaca; margin-bottom: 1.5rem; font-weight: 600;">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- MODAL / FORM TAMBAH USER -->
        <div id="modalTambahUser" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
            <div style="background: white; padding: 2rem; border-radius: 16px; width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #1f2937;">Tambah User Baru</h3>
                <p style="font-size: 0.85rem; color: #6b7280; margin-bottom: 1.5rem;">Masukkan detail akun baru untuk Super Admin, Admin, atau Siswa.</p>
                
                <form action="" method="POST">
                    <input type="hidden" name="action" value="tambah_user">
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem;">Nama Lengkap</label>
                        <input type="text" name="nama" placeholder="Nama Lengkap" required style="width: 100%; padding: 0.7rem; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem;">Email Sekolah</label>
                        <input type="email" name="email" placeholder="nama@stella.sch.id" required style="width: 100%; padding: 0.7rem; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem;">Password</label>
                        <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 0.7rem; border: 1px solid #d1d5db; border-radius: 8px; box-sizing: border-box;">
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem;">Role / Akses</label>
                        <select name="role" required style="width: 100%; padding: 0.7rem; border: 1px solid #d1d5db; border-radius: 8px; font-family: inherit; background: white; box-sizing: border-box;">
                            <option value="siswa">Siswa</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 0.75rem;">
                        <button type="submit" style="flex: 1; background: #800020; color: white; padding: 0.7rem; border-radius: 8px; border: none; font-weight: 700; cursor: pointer;">Simpan User</button>
                        <button type="button" onclick="document.getElementById('modalTambahUser').style.display='none';" style="flex: 1; background: #e5e7eb; color: #1f2937; border: none; padding: 0.7rem; border-radius: 8px; font-weight: 600; cursor: pointer;">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KOTAK TABEL DAFTAR AKUN -->
        <div style="background: white; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 1.15rem; font-weight: 700; color: #1f2937; margin: 0;">Daftar Akun Terdaftar</h2>
                <span style="font-size: 0.85rem; color: #6b7280;">Total User: <?= $total_users; ?></span>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #800020; color: white; border-bottom: 1px solid #e5e7eb;">
                            <th style="padding: 1rem 1.5rem; font-weight: 600;">No</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600;">Nama Lengkap</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600;">Email Sekolah</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600;">Role</th>
                            <th style="padding: 1rem 1.5rem; font-weight: 600; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php 
                            $no = 1; 
                            foreach ($users as $u): 
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem 1.5rem; color: #6b7280;"><?= $no++; ?></td>
                                    <td style="padding: 1rem 1.5rem; font-weight: 600; color: #1f2937;"><?= htmlspecialchars($u['nama']); ?></td>
                                    <td style="padding: 1rem 1.5rem; color: #4b5563;"><?= htmlspecialchars($u['email']); ?></td>
                                    <td style="padding: 1rem 1.5rem;">
                                        <span style="background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.75rem; font-weight: 700;">
                                            <?= strtoupper($u['role']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem 1.5rem; text-align: center;">
                                        <?php if ($u['role'] === 'super_admin' && $u['id'] == 1): ?>
                                            <span style="color: #9ca3af; font-size: 0.8rem; font-style: italic; font-weight: 600;">Akun Utama</span>
                                        <?php else: ?>
                                            <a href="../controllers/userController.php?action=hapus&id=<?= $u['id']; ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" style="background: #dc2626; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-decoration: none;">Hapus</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="padding: 2rem; text-align: center; color: #6b7280;">Belum ada user terdaftar.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <footer>
        © 2026 Etalase Digital Stella • Baiq Salva Sheyna Arfani
    </footer>
</body>
</html>