<?php
session_start();
require_once '../config/database.php';

// Proteksi Login
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// 1. Logika Unggah File
if (isset($_POST['upload_file'])) {
    $judul = $conn->real_escape_string($_POST['judul_dokumen']);
    $tipe  = $_POST['tipe_dokumen'];
    $adminID = $_SESSION['userID'];

    // Proses Upload Fisik
    $targetDir = "../uploads/dokumen/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . '_' . basename($_FILES["file_dokumen"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    if (move_uploaded_file($_FILES["file_dokumen"]["tmp_name"], $targetFilePath)) {
        // Simpan ke Database (Sesuai Class Diagram: Tabel Dokumen)
        $sql = "INSERT INTO dokumen (judul_dokumen, tipe_dokumen, file_path, uploadBy, tanggal_upload) 
                VALUES ('$judul', '$tipe', '$fileName', '$adminID', NOW())";
        $conn->query($sql);
        header("Location: manage_file.php?msg=success");
    } else {
        header("Location: manage_file.php?msg=error");
    }
}

// 2. Logika Hapus File
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Ambil nama file untuk dihapus secara fisik
    $res = $conn->query("SELECT file_path FROM dokumen WHERE dokumenID = $id");
    if ($res && $res->num_rows > 0) {
        $file = $res->fetch_assoc();
        if ($file && !empty($file['file_path'])) {
            @unlink("../uploads/dokumen/" . $file['file_path']);
        }
    }

    $conn->query("DELETE FROM dokumen WHERE dokumenID = $id");
    header("Location: manage_file.php?msg=deleted");
}

// 3. Ambil Data Dokumen
$result = $conn->query("SELECT d.*, u.namaLengkap FROM dokumen d JOIN users u ON d.uploadBy = u.userID ORDER BY d.dokumenID DESC");
$query_error = null;
if ($result === false) {
    $query_error = $conn->error;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen File | Wesclic Habits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-green: #146332;
            --active-green: #22c55e;
            --sidebar-bg: var(--sidebar-green, #146332);
            --sidebar-active: var(--active-green, #22c55e);
            --main-bg: #F8FAFC;
            --white: #FFFFFF;
            --border: #E2E8F0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--main-bg); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar Collapse Style */
        .sidebar { width: 280px; background-color: var(--sidebar-green); color: white; display: flex; flex-direction: column; padding: 30px 20px; transition: 0.3s; position: relative; }
        .sidebar.collapsed { width: 80px; padding: 30px 15px; }
        .sidebar.collapsed .nav-text, .sidebar.collapsed .brand-text { display: none; }
        .nav-link { text-decoration: none; color: rgba(255,255,255,0.7); padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; gap: 12px; font-size: 14px; margin-bottom: 8px; transition: 0.3s; white-space: nowrap; }
        .nav-link.active { background-color: var(--active-green); color: white; }
        .sidebar-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); cursor: pointer; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-nav { height: 80px; background: var(--white); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }
        
        .content { padding: 30px 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-upload { background: var(--active-green); color: white; border: none; padding: 12px 24px; border-radius: 30px; font-weight: 600; cursor: pointer; font-size: 14px; }
        /* Table Style */
        .table-card { background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #F8FAFC; padding: 16px 24px; text-align: left; font-size: 12px; color: #64748B; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 18px 24px; font-size: 14px; border-bottom: 1px solid #F1F5F9; }

        .file-icon { display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .badge-type { background: #F1F5F9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }

        /* Modal */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 100; }
        .modal-box { background: white; padding: 30px; border-radius: 20px; width: 450px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; margin-bottom: 8px; color: #64748B; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 10px; }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M4 4v16M20 4v16M4 12h16"></path></svg>
            <h2 class="brand-text">Habits Tracking</h2>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
            <a href="manage_users.php" class="nav-link"><span class="nav-text">Manajemen User</span></a>
            <a href="manage_cultures.php" class="nav-link"><span class="nav-text">Manajemen Culture</span></a>
            <a href="manage_file.php" class="nav-link active"><span class="nav-text">Manajemen File</span></a>
            <a href="manage_notifications.php" class="nav-link"><span class="nav-text">Notifikasi</span></a>
            <a href="reports.php" class="nav-link"><span class="nav-text">Laporan & Statistik</span></a>
            <a href="pengaturan.php" class="nav-link"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Logout</span></a>
        </nav>
        <div class="sidebar-footer" onclick="toggleSidebar()">
            <span id="collapse-btn">« Collapse</span>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <button type="button" class="burger" onclick="toggleSidebar()" style="background:none;border:none;color:var(--text-dark);font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;">☰ Menu</button>
            <div style="font-size: 13px; color: #64748B;">Dashboard > <strong>Manajemen File</strong></div>
            <div style="font-weight: 600;">Halo, Admin</div>
        </header>

        <div class="content">
            <div class="header-flex">
                <h1>Pusat Dokumen (File Center)</h1>
                <button class="btn-upload" onclick="openModal()">+ Unggah Dokumen</button>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Dokumen</th>
                            <th>Tipe</th>
                            <th>Diunggah Oleh</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                            <tbody>
                        <?php if ($result): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="file-icon">
                                        📄 <?= $row['judul_dokumen'] ?>
                                    </td>
                                    <td><span class="badge-type"><?= $row['tipe_dokumen'] ?></span></td>
                                    <td style="color: #64748B;"><?= $row['namaLengkap'] ?></td>
                                    <td style="font-size: 13px;"><?= !empty($row['tanggal_upload']) ? date('d M Y', strtotime($row['tanggal_upload'])) : '-' ?></td>
                                    <td>
                                        <a href="../uploads/dokumen/<?= $row['file_path'] ?>" target="_blank" style="color: var(--active-green); text-decoration: none; font-weight: 600; margin-right: 15px;">Lihat</a>
                                        <a href="manage_file.php?delete=<?= $row['dokumenID'] ?>" style="color: #EF4444; text-decoration: none;" onclick="return confirm('Hapus file ini secara permanen?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="color: #EF4444; padding: 18px 24px;">Terjadi kesalahan saat memuat data: <?= htmlspecialchars($query_error) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal" id="modalUpload">
        <div class="modal-box">
            <h3 style="margin-bottom: 20px;">Unggah File Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Judul Dokumen</label>
                    <input type="text" name="judul_dokumen" placeholder="Contoh: SOP Berdoa Pagi" required>
                </div>
                <div class="form-group">
                    <label>Kategori Dokumen</label>
                    <select name="tipe_dokumen">
                        <option value="SOP">SOP</option>
                        <option value="Doa">Doa</option>
                        <option value="Panduan">Panduan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pilih Berkas (PDF/Doc)</label>
                    <input type="file" name="file_dokumen" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="closeModal()" style="background: none; border: 1px solid #ddd; padding: 10px 20px; border-radius: 10px; cursor: pointer;">Batal</button>
                    <button type="submit" name="upload_file" class="btn-upload">Unggah Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const btn = document.getElementById('collapse-btn');
            sb.classList.toggle('collapsed');
            btn.innerHTML = sb.classList.contains('collapsed') ? '»' : '« Collapse';
        }
        function openModal() { document.getElementById('modalUpload').style.display = 'flex'; }
        function closeModal() { document.getElementById('modalUpload').style.display = 'none'; }
    </script>
</body>
</html>