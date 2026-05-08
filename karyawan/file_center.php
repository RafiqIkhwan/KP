<?php
session_start();
require_once '../config/database.php';

// Proteksi Login Karyawan
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Karyawan') {
    header("Location: ../index.php");
    exit();
}

// 1. Logika Pagination
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_data = $conn->query("SELECT COUNT(*) AS total FROM dokumen")->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// 2. Ambil Data Dokumen (Hanya yang statusnya aktif/publik jika ada)
$sql = "SELECT * FROM dokumen ORDER BY tanggal_upload DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>File Center | Wesclic Habits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-green: #146332;
            --active-green: #22c55e;
            --main-bg: #F8FAFC;
            --white: #FFFFFF;
            --border: #E2E8F0;
            --text-dark: #1E293B;
            --text-muted: #64748B;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--main-bg); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar Collapse */
        .sidebar { width: 280px; background-color: var(--sidebar-green); color: white; display: flex; flex-direction: column; padding: 30px 20px; transition: 0.3s; position: relative; }
        .sidebar.collapsed { width: 80px; padding: 30px 15px; }
        .sidebar.collapsed .nav-text, .sidebar.collapsed .brand-text { display: none; }
        .nav-link { text-decoration: none; color: rgba(255,255,255,0.7); padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; gap: 12px; font-size: 14px; margin-bottom: 8px; transition: 0.3s; white-space: nowrap; }
        .nav-link.active { background-color: var(--active-green); color: white; font-weight: 600; }
        .sidebar-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); cursor: pointer; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-nav { height: 80px; background: var(--white); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }
        .content { padding: 30px 40px; }

        /* Table Card & Filter */
        .table-card { background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .filter-bar { padding: 20px 24px; display: flex; gap: 15px; border-bottom: 1px solid var(--border); background: #F8FAFC; }
        .search-input { flex: 1; padding: 10px 20px; border-radius: 30px; border: 1px solid var(--border); outline: none; font-size: 13px; }
        .select-filter { padding: 10px 15px; border-radius: 10px; border: 1px solid var(--border); font-size: 12px; color: var(--text-muted); outline: none; }

        table { width: 100%; border-collapse: collapse; }
        th { background: var(--active-green); color: white; padding: 16px 24px; text-align: left; font-size: 13px; font-weight: 700; }
        td { padding: 18px 24px; font-size: 14px; border-bottom: 1px solid #F1F5F9; }

        /* Badge Styles */
        .type-badge { padding: 6px 15px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-block; }
        .pdf { background: #FEE2E2; color: #991B1B; }
        .word { background: #DBEAFE; color: #1e40af; }
        .csv { background: #DCFCE7; color: #166534; }

        .action-icon { color: #4285F4; text-decoration: none; font-size: 18px; margin-left: 10px; }

        /* Pagination */
        .pagination { display: flex; justify-content: flex-end; padding: 24px; gap: 8px; }
        .pg-item { text-decoration: none; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid var(--border); color: var(--text-muted); font-size: 13px; }
        .pg-item.active { background: #4285F4; color: white; border-color: #4285F4; }
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
            <a href="laporan_saya.php" class="nav-link"><span class="nav-text">Laporan Saya</span></a>
            <a href="file_center.php" class="nav-link active"><span class="nav-text">File Center</span></a>
            <a href="settings.php" class="nav-link"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Logout</span></a>
        </nav>
        <div class="sidebar-footer" onclick="toggleSidebar()"><span id="collapse-btn">« Collapse</span></div>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <div style="font-size: 12px; color: var(--text-muted);">Dashboard > <strong>Files</strong></div>
            <div style="font-weight: 600;">Halo, <?= explode(' ', $_SESSION['namaLengkap'])[0] ?></div>
        </header>

        <div class="content">
            <h1 style="font-size: 24px; margin-bottom: 5px;">Dokumen & Panduan</h1>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">Akses semua SOP dan materi budaya perusahaan di sini</p>

            <div class="table-card">
                <div class="filter-bar">
                    <input type="text" class="search-input" placeholder="🔍 Cari Nama...">
                    <select class="select-filter"><option>Semua Tipe</option></select>
                    <select class="select-filter"><option>Semua Kategori</option></select>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Deskripsi</th>
                            <th>Tipe File</th>
                            <th>Tanggal Upload</th>
                            <th>Kategori</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $ext = pathinfo($row['file_path'], PATHINFO_EXTENSION);
                                $badgeClass = (strtolower($ext) == 'pdf') ? 'pdf' : (in_array(strtolower($ext), ['doc','docx']) ? 'word' : 'csv');
                            ?>
                            <tr>
                                <td style="font-weight: 600;"><?= $row['judul_dokumen'] ?></td>
                                <td style="color: var(--text-muted); font-size: 12px; max-width: 250px;">Panduan standar operasional untuk semua karyawan</td>
                                <td><span class="type-badge <?= $badgeClass ?>"><?= strtoupper($ext) ?></span></td>
                                <td><?= date('d November Y', strtotime($row['tanggal_upload'])) ?></td>
                                <td>SOP & Regulasi</td>
                                <td>
                                    <a href="../uploads/dokumen/<?= $row['file_path'] ?>" target="_blank" class="action-icon">👁️</a>
                                    <a href="../uploads/dokumen/<?= $row['file_path'] ?>" download class="action-icon">📥</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; padding: 40px; color: var(--text-muted);">Belum ada dokumen tersedia.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <a href="?page=<?= max(1, $page-1) ?>" class="pg-item">&lt;</a>
                    <?php for($i=1; $i<=$total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="pg-item <?= ($i==$page)?'active':'' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <a href="?page=<?= min($total_pages, $page+1) ?>" class="pg-item">&gt;</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const btn = document.getElementById('collapse-btn');
            sb.classList.toggle('collapsed');
            btn.innerHTML = sb.classList.contains('collapsed') ? '»' : '« Collapse';
        }
    </script>
</body>
</html>