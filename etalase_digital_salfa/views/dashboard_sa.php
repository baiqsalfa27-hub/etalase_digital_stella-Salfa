<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

// Pastikan hanya Super Admin yang bisa mengakses
if (!isset($_SESSION['role']) || (strtolower(trim($_SESSION['role'])) !== 'super_admin' && strtolower(trim($_SESSION['role'])) !== 'superadmin')) {
    header("Location: login.php?error=" . urlencode("Akses ditolak. Khusus Super Admin."));
    exit();
}

$nama_sa = $_SESSION['nama'] ?? 'Super Admin';
$cari = trim($_GET['cari'] ?? '');

try {
    if (!empty($cari)) {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE nama_project LIKE ? OR deskripsi LIKE ? ORDER BY id ASC");
        $stmt->execute(["%$cari%", "%$cari%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM projects ORDER BY id ASC");
    }
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_karya = count($projects);
} catch (Exception $e) {
    $total_karya = 0;
    $projects = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Super Admin - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/dashboard_sa.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="dashboard_sa.php" class="brand">
                <img src="../img/logo-ts2.png" alt="Logo" onerror="this.style.display='none'">
                <span>Etalase Digital Stella</span>
            </a>
        </div>
        <div class="nav-links">
            <a href="dashboard_sa.php" class="nav-item active">Daftar Karya</a>
            <a href="user.php" class="nav-item">Kelola User</a>
            <div class="user-profile-box" style="border-left: 1px solid #e5e7eb; padding-left: 1rem;">
                <span class="user-name"><?= htmlspecialchars($nama_sa); ?></span>
                <span class="user-role">SUPER ADMIN</span>
            </div>
            <a href="../controllers/authController.php?action=logout" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="sa-container">
        <div class="sa-hero-banner">
            <div>
                <h1>Halo, <?= htmlspecialchars($nama_sa); ?> 👋</h1>
                <p>Panel kendali penuh Super Admin untuk pengelolaan galeri karya dan pengguna</p>
                <div style="margin-top: 0.8rem;">
                    <a href="project.php?action=tambah" class="btn" style="display: inline-block; background: white; color: #800020; font-weight: 700; padding: 0.5rem 1.1rem; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-size: 0.85rem;">+ Tambah Karya Baru</a>
                </div>
            </div>
            
            <div class="sa-banner-right">
                <form action="dashboard_sa.php" method="GET" style="display: flex; gap: 0.5rem; align-items: center; margin: 0; width: auto; flex-shrink: 0;">
                    <input type="text" name="cari" value="<?= htmlspecialchars($cari); ?>" placeholder="Cari karya..." style="padding: 0.55rem 1rem; border-radius: 8px; border: none; width: 200px; min-width: 200px; max-width: 200px; outline: none; background: white; color: #1f2937; font-size: 0.85rem; box-sizing: border-box;">
                    <button type="submit" style="background: white; color: #800020; font-weight: 700; padding: 0.55rem 1.1rem; border-radius: 8px; border: none; cursor: pointer; font-size: 0.85rem; white-space: nowrap;">Cari</button>
                    <?php if (!empty($cari)): ?>
                        <a href="dashboard_sa.php" style="background: #5c061c; color: white; padding: 0.55rem 0.85rem; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; font-size: 0.8rem; white-space: nowrap;">Reset</a>
                    <?php endif; ?>
                </form>
                <div class="sa-total-badge">
                    Total Karya: <?= $total_karya; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="sa-container" style="margin-bottom: 1rem;">
            <div style="background: #f0fdf4; color: #16a34a; padding: 0.85rem; border-radius: 10px; border: 1px solid #bbf7d0; font-weight: 600; font-size: 0.9rem;">
                <?= htmlspecialchars($_GET['success']); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="sa-content-wrapper">
        <h2 class="sa-section-title">
            <?php if (!empty($cari)): ?>
                Hasil Pencarian untuk: "<?= htmlspecialchars($cari); ?>"
            <?php else: ?>
                Daftar Seluruh Karya Siswa
            <?php endif; ?>
        </h2>

        <?php if (count($projects) > 0): ?>
            <div class="sa-grid">
                <?php foreach ($projects as $row): ?>
                    <?php 
                        $judul_tampil = $row['nama_project'] ?? ($row['judul'] ?? 'Tanpa Judul');
                        $link_gh = $row['link_github'] ?? '#';
                        $link_dm = $row['link_demo'] ?? '#';
                        $tanggal_proj = $row['created_at'] ?? date('Y-m-d H:i:s');
                        $img_path = (!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])) ? '../uploads/' . htmlspecialchars($row['gambar']) : '';
                        $deskripsi_proj = htmlspecialchars($row['deskripsi'] ?? 'Tidak ada deskripsi.');
                        $formatted_date = date('d M Y', strtotime($tanggal_proj));
                    ?>
                    <div class="sa-card">
                        <div class="sa-card-img" onclick="showDetailModal('<?= $img_path; ?>', '<?= addslashes($judul_tampil); ?>', 'Super Admin', '<?= $formatted_date; ?>', '<?= addslashes($deskripsi_proj); ?>', '<?= htmlspecialchars($link_gh); ?>', '<?= htmlspecialchars($link_dm); ?>')">
                            <?php if (!empty($img_path)): ?>
                                <img src="<?= $img_path; ?>" alt="Preview">
                            <?php else: ?>
                                <div style="color: #6b7280; font-size: 0.8rem;">Tidak ada gambar</div>
                            <?php endif; ?>
                        </div>
                        <div class="sa-card-body">
                            <h3 class="sa-card-title" onclick="showDetailModal('<?= $img_path; ?>', '<?= addslashes($judul_tampil); ?>', 'Super Admin', '<?= $formatted_date; ?>', '<?= addslashes($deskripsi_proj); ?>', '<?= htmlspecialchars($link_gh); ?>', '<?= htmlspecialchars($link_dm); ?>')"><?= htmlspecialchars($judul_tampil); ?></h3>
                            <p class="sa-card-desc"><?= htmlspecialchars($row['deskripsi'] ?? ''); ?></p>
                            
                            <div class="sa-btn-group">
                                <a href="<?= htmlspecialchars($link_gh); ?>" target="_blank" class="sa-btn-github">GitHub</a>
                                <a href="<?= htmlspecialchars($link_dm); ?>" target="_blank" class="sa-btn-demo">Live Demo</a>
                            </div>

                            <div class="sa-card-footer">
                                <div class="sa-author-info">
                                    Oleh: <span>Siswa</span><br>
                                    <span style="font-size: 0.65rem; font-weight: 700; color: #374151;"><?= $formatted_date; ?></span>
                                </div>
                                <div class="sa-action-buttons">
                                    <a href="project.php?action=edit&id=<?= $row['id']; ?>" class="sa-btn-edit">Edit</a>
                                    <a href="#" onclick="showDeleteModal('../controllers/projectController.php?action=hapus&id=<?= $row['id']; ?>')" class="sa-btn-hapus">Hapus</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 3rem; text-align: center; color: #4b5563; font-weight: 600; font-size: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <?php if (!empty($cari)): ?>
                    Oops! Karya yang kamu cari tidak ditemukan.
                <?php else: ?>
                    Belum ada karya apapun yang diunggah.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- MODAL DETAIL -->
    <div id="detailModal" class="sa-modal-overlay">
        <div class="sa-modal-box">
            <button class="sa-modal-close" onclick="closeDetailModal()">&times;</button>
            <div class="sa-modal-img" id="modalImgContainer">
                <img id="modalImg" src="" alt="Detail Gambar">
            </div>
            <h3 class="sa-modal-title" id="modalTitle"></h3>
            <div class="sa-modal-info" id="modalInfo"></div>
            <p class="sa-modal-desc" id="modalDesc"></p>
            <div class="sa-modal-actions">
                <a id="modalGithubBtn" href="#" target="_blank" class="sa-btn-github" style="padding: 0.5rem; text-align: center;">Buka GitHub</a>
                <a id="modalDemoBtn" href="#" target="_blank" class="sa-btn-demo" style="padding: 0.5rem; text-align: center;">Buka Live Demo</a>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS -->
    <div id="deleteModal" class="sa-modal-overlay">
        <div class="sa-modal-box" style="max-width: 380px; text-align: center;">
            <h3 style="margin-bottom:0.75rem; color:#1f2937; font-size:1.2rem;">Konfirmasi Hapus</h3>
            <p style="color:#6b7280; font-size:0.9rem; margin-bottom:1.5rem;">Apakah kamu yakin ingin menghapus data karya ini?</p>
            <div style="display:flex; gap:0.75rem; justify-content:center;">
                <button onclick="closeDeleteModal()" style="background:#e5e7eb; color:#374151; border:none; padding:0.5rem 1rem; border-radius:8px; font-weight:600; cursor:pointer;">Batal</button>
                <a id="confirmDeleteBtn" href="#" style="background:#dc2626; color:white; border:none; padding:0.5rem 1rem; border-radius:8px; font-weight:600; text-decoration:none; display:inline-block;">Ya, Hapus</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <footer>© 2026 Etalase Digital Stella • Baiq Salva Sheyna Arfani</footer>
</body>
</html>