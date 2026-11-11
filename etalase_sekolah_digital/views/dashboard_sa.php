<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php?error=" . urlencode("Akses ditolak. Khusus Super Admin."));
    exit();
}

$nama_sa = $_SESSION['nama'] ?? 'Super Admin';
$cari = trim($_GET['cari'] ?? '');
$table_name = 'projects';

try {
    $total_query = $pdo->query("SELECT COUNT(*) FROM $table_name");
    $total_karya = $total_query->fetchColumn();

    if (!empty($cari)) {
        $stmt = $pdo->prepare("SELECT p.*, u.nama as nama_siswa FROM $table_name p LEFT JOIN users u ON (p.id_user = u.id OR p.user_id = u.id) WHERE p.nama_project LIKE ? OR p.deskripsi LIKE ? OR u.nama LIKE ? ORDER BY p.id ASC");
        $stmt->execute(["%$cari%", "%$cari%", "%$cari%"]);
    } else {
        $stmt = $pdo->query("SELECT p.*, u.nama as nama_siswa FROM $table_name p LEFT JOIN users u ON (p.id_user = u.id OR p.user_id = u.id) ORDER BY p.id ASC");
    }
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $projects = [];
    $total_karya = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Super Admin - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            <a href="users.php" class="nav-item">Kelola User</a>
            <div class="user-profile-box">
                <span class="user-name"><?= htmlspecialchars($nama_sa); ?></span>
                <span class="user-role">SUPER ADMIN</span>
            </div>
            <a href="../controllers/authController.php?action=logout" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="sa-container">
        <!-- HERO BANNER -->
        <div class="sa-hero-banner">
            <div>
                <h1>Halo, <?= htmlspecialchars($nama_sa); ?> 👋</h1>
                <p>Pusat galeri dan dokumentasi karya inovatif siswa berprestasi serta manajemen sistem</p>
                <div style="margin-top: 0.8rem;">
                    <a href="project.php?action=tambah" class="btn" style="display: inline-block; background: white; color: #800020; font-weight: 700; padding: 0.5rem 1.1rem; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-size: 0.85rem;">+ Tambah Karya Baru</a>
                </div>
            </div>
            
            <div class="sa-banner-right">
                <form action="dashboard_sa.php" method="GET" style="display: flex; gap: 0.5rem; align-items: center; margin: 0;">
                    <input type="text" name="cari" value="<?= htmlspecialchars($cari); ?>" placeholder="Cari karya..." style="padding: 0.55rem 1rem; border-radius: 8px; border: none; width: 180px; outline: none; background: white; color: #1f2937; font-size: 0.85rem;">
                    <button type="submit" style="background: white; color: #800020; font-weight: 700; padding: 0.55rem 1rem; border-radius: 8px; border: none; cursor: pointer; font-size: 0.85rem;">Cari</button>
                    <?php if (!empty($cari)): ?>
                        <a href="dashboard_sa.php" style="background: #5c061c; color: white; padding: 0.55rem 0.85rem; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; font-size: 0.8rem;">Reset</a>
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

    <!-- KONTEN UTAMA KARYA -->
    <div class="sa-content-wrapper">
        <h2 class="sa-section-title">
            <?php if (!empty($cari)): ?>
                Hasil Pencarian untuk: "<?= htmlspecialchars($cari); ?>"
            <?php else: ?>
                Semua Karya Proyek Siswa
            <?php endif; ?>
        </h2>

        <div class="sa-grid">
            <?php if (count($projects) > 0): ?>
                <?php foreach ($projects as $row): ?>
                    <?php 
                        $judul_tampil = $row['nama_project'] ?? ($row['judul'] ?? ($row['judulkarya'] ?? 'Tanpa Judul'));
                        $link_gh = $row['link_github'] ?? ($row['github'] ?? '#');
                        $link_dm = $row['link_demo'] ?? ($row['live_demo'] ?? '#');
                        $tanggal_proj = $row['created_at'] ?? ($row['tanggal'] ?? date('Y-m-d H:i:s'));
                        $img_path = (!empty($row['gambar']) && file_exists('../uploads/' . $row['gambar'])) ? '../uploads/' . htmlspecialchars($row['gambar']) : '';
                        $nama_siswa = htmlspecialchars($row['nama_siswa'] ?? 'Siswa');
                        $deskripsi_proj = htmlspecialchars($row['deskripsi'] ?? 'Tidak ada deskripsi.');
                        $formatted_date = date('d M Y', strtotime($tanggal_proj));
                    ?>
                    <div class="sa-card">
                        <!-- Foto diklik akan membuka Pop-up Detail -->
                        <div class="sa-card-img" onclick="showDetailModal('<?= $img_path; ?>', '<?= addslashes($judul_tampil); ?>', '<?= addslashes($nama_siswa); ?>', '<?= $formatted_date; ?>', '<?= addslashes($deskripsi_proj); ?>', '<?= htmlspecialchars($link_gh); ?>', '<?= htmlspecialchars($link_dm); ?>')">
                            <?php if (!empty($img_path)): ?>
                                <img src="<?= $img_path; ?>" alt="Preview">
                            <?php else: ?>
                                <div style="color: #6b7280; font-size: 0.8rem;">Tidak ada gambar</div>
                            <?php endif; ?>
                        </div>
                        <div class="sa-card-body">
                            <!-- Judul diklik juga bisa membuka Pop-up Detail -->
                            <h3 class="sa-card-title" onclick="showDetailModal('<?= $img_path; ?>', '<?= addslashes($judul_tampil); ?>', '<?= addslashes($nama_siswa); ?>', '<?= $formatted_date; ?>', '<?= addslashes($deskripsi_proj); ?>', '<?= htmlspecialchars($link_gh); ?>', '<?= htmlspecialchars($link_dm); ?>')"><?= htmlspecialchars($judul_tampil); ?></h3>
                            <p class="sa-card-desc"><?= htmlspecialchars($row['deskripsi'] ?? ''); ?></p>
                            
                            <div class="sa-btn-group">
                                <a href="<?= htmlspecialchars($link_gh); ?>" target="_blank" class="sa-btn-github">GitHub</a>
                                <a href="<?= htmlspecialchars($link_dm); ?>" target="_blank" class="sa-btn-demo">Live Demo</a>
                            </div>

                            <div class="sa-card-footer">
                                <div class="sa-author-info">
                                    Oleh: <span><?= $nama_siswa; ?></span><br>
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
            <?php else: ?>
                <div class="sa-empty-box">
                    <p style="margin: 0; font-weight: 600; color: #1f2937;">Oops! Karya yang kamu cari tidak ditemukan.</p>
                    <p style="margin: 0.3rem 0 0 0; font-size: 0.8rem;">Coba gunakan kata kunci lain atau reset pencarian.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL POP-UP DETAIL KARYA -->
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

    <!-- MODAL POP-UP KONFIRMASI HAPUS -->
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

    <script>
        // Fungsi Pop-up Detail Karya
        function showDetailModal(imgSrc, title, author, date, desc, githubUrl, demoUrl) {
            const imgContainer = document.getElementById('modalImgContainer');
            const imgElement = document.getElementById('modalImg');
            
            if (imgSrc && imgSrc !== '') {
                imgElement.src = imgSrc;
                imgContainer.style.display = 'flex';
            } else {
                imgContainer.style.display = 'none';
            }

            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalInfo').innerText = 'Oleh: ' + author + ' • ' + date;
            document.getElementById('modalDesc').innerText = desc;
            
            document.getElementById('modalGithubBtn').setAttribute('href', githubUrl);
            document.getElementById('modalDemoBtn').setAttribute('href', demoUrl);

            document.getElementById('detailModal').style.display = 'flex';
        }

        function closeDetailModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        // Fungsi Pop-up Hapus
        function showDeleteModal(url) {
            document.getElementById('confirmDeleteBtn').setAttribute('href', url);
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Tutup modal jika klik di luar area kotak modal
        window.onclick = function(event) {
            let detailModal = document.getElementById('detailModal');
            let deleteModal = document.getElementById('deleteModal');
            if (event.target === detailModal) {
                detailModal.style.display = 'none';
            }
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>

    <footer>© 2026 Etalase Digital Stella • Baiq Salva Sheyna Arfani</footer>
</body>
</html>