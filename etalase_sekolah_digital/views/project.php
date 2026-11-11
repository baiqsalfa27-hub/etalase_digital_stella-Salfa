<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$action = $_GET['action'] ?? 'tambah';
$id_project = $_GET['id'] ?? 0;
$project = ['nama_project' => '', 'deskripsi' => '', 'link_github' => '', 'link_demo' => '', 'gambar' => ''];

if ($action == 'edit' && $id_project > 0) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id_project]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$project) {
        echo "Data karya tidak ditemukan!";
        exit();
    }
}

$dashboard_link = ($_SESSION['role'] == 'siswa') ? 'dashboard_siswa.php' : (($_SESSION['role'] == 'admin') ? 'dashboard_admin.php' : 'dashboard_sa.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= ($action == 'edit') ? 'Edit Karya' : 'Tambah Karya'; ?> - Etalase Digital Stella</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard_sa.css?v=<?php echo time(); ?>">
    <style>
        /* Styling khusus form agar rapi dan senada */
        .form-container {
            max-width: 650px;
            background: #ffffff;
            margin: 2.5rem auto;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group textarea,
        .form-group input[type="file"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9rem;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #800020;
        }
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn-submit {
            background: #800020;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: #5c061c;
        }
        .btn-cancel {
            background: #e5e7eb;
            color: #374151;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.95rem;
            display: inline-block;
            text-align: center;
        }
        .btn-cancel:hover {
            background: #d1d5db;
        }
    </style>
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
            <a href="../controllers/authController.php?action=logout" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="sa-container">
        <div class="form-container">
            <h2 style="color: #800020; margin-top: 0; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 700;">
                <?= ($action == 'edit') ? 'Edit Karya Proyek' : 'Tambah Karya Proyek Baru'; ?>
            </h2>

            <?php if (isset($_GET['error'])): ?>
                <div style="background: #fef2f2; color: #dc2626; padding: 0.85rem; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 1.2rem; font-size: 0.9rem; font-weight: 600;">
                    <?= htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="../controllers/projectController.php?action=<?= ($action == 'edit') ? 'update' : 'store'; ?>" method="POST" enctype="multipart/form-data">
                
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $project['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nama Proyek / Karya</label>
                    <input type="text" name="nama_project" value="<?= htmlspecialchars($project['nama_project']); ?>" placeholder="Contoh: Aplikasi Kasir Toko Sembako" required>
                </div>

                <div class="form-group">
                    <label>Deskripsi Proyek</label>
                    <textarea name="deskripsi" placeholder="Jelaskan secara singkat tentang fitur dan fungsi karya ini..." required><?= htmlspecialchars($project['deskripsi']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Link Repository GitHub</label>
                    <input type="url" name="link_github" value="<?= htmlspecialchars($project['link_github']); ?>" placeholder="https://github.com/username/repo">
                </div>

                <div class="form-group">
                    <label>Link Live Demo (Opsional)</label>
                    <input type="url" name="link_demo" value="<?= htmlspecialchars($project['link_demo']); ?>" placeholder="https://domain-project.vercel.app">
                </div>

                <div class="form-group">
                    <label>Upload Gambar / Screenshot Karya <?= ($action == 'edit') ? '(Biarkan kosong jika tidak ingin mengubah gambar)' : ''; ?></label>
                    <?php if ($action == 'edit' && !empty($project['gambar'])): ?>
                        <div style="margin-bottom: 0.75rem;">
                            <img src="../uploads/<?= htmlspecialchars($project['gambar']); ?>" alt="Current Image" style="max-height: 100px; border-radius: 6px; border: 1px solid #e5e7eb;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="gambar" accept=".jpg, .jpeg, .png, .webp">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit"><?= ($action == 'edit') ? 'Simpan Perubahan' : 'Tambah Karya'; ?></button>
                    <a href="<?= $dashboard_link; ?>" class="btn-cancel">Batal</a>
                </div>

            </form>
        </div>
    </div>

    <footer>© 2026 Etalase Digital Stella • Baiq Salva Sheyna Arfani</footer>
</body>
</html>