<?php
session_start();
require_once '../config/database.php';

// Proteksi Login
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// 1. Logika Tambah Culture
if (isset($_POST['add_culture'])) {
    $nama = $conn->real_escape_string($_POST['namaCulture']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $frekuensi = $_POST['frekuensi'];
    $bobot = $_POST['bobotNilai'];

    $sql = "INSERT INTO culture (namaCulture, deskripsi, frekuensi, bobotNilai, tanggalDibuat) 
            VALUES ('$nama', '$deskripsi', '$frekuensi', '$bobot', CURDATE())";
    $conn->query($sql);
    header("Location: manage_cultures.php?msg=added");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM culture WHERE cultureID = $id");
    header("Location: manage_cultures.php?msg=deleted");
    exit();
}

// 3. Ambil Data Culture
$result = $conn->query("SELECT * FROM culture ORDER BY cultureID DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Culture | Wesclic</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-green: #146332;
            --active-green: #22c55e;
            --sidebar-bg: var(--sidebar-green, #146332);
            --sidebar-active: var(--active-green, #22c55e);
            --main-bg: #F8FAFC;
            --white: #FFFFFF;
            --text-dark: #1E293B;
            --border: #E2E8F0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--main-bg); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar Bergeser */
        .sidebar { width: 280px; background-color: var(--sidebar-green); color: white; display: flex; flex-direction: column; padding: 30px 20px; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; }
        .sidebar.collapsed { width: 80px; padding: 30px 15px; }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; white-space: nowrap; overflow: hidden; }
        .nav-link { text-decoration: none; color: rgba(255,255,255,0.7); padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; gap: 12px; font-size: 14px; margin-bottom: 8px; transition: 0.3s; white-space: nowrap; }
        .nav-link.active { background-color: var(--active-green); color: white; }
        .sidebar.collapsed .nav-text, .sidebar.collapsed .brand-text { display: none; }
        .sidebar-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); cursor: pointer; font-size: 13px; }

        /* Content Area */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-nav { height: 80px; background: var(--white); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }
        .content { padding: 30px 40px; }

        /* Table & Cards */
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-add { background: var(--active-green); color: white; border: none; padding: 12px 24px; border-radius: 30px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 14px; }
        
        .table-card { background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #F8FAFC; padding: 16px 24px; text-align: left; font-size: 12px; color: #64748B; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 18px 24px; font-size: 14px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }

        .badge-freq { background: #ECFDF5; color: #065F46; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; }
        
        /* Modal */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 100; }
        .modal-box { background: white; padding: 30px; border-radius: 20px; width: 450px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; margin-bottom: 5px; color: #64748B; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 10px; }
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
            <a href="manage_cultures.php" class="nav-link active"><span class="nav-text">Manajemen Culture</span></a>
            <a href="manage_file.php" class="nav-link"><span class="nav-text">Manajemen File</span></a>
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
            <div style="font-size: 13px; color: #64748B;">Dashboard > <strong>Manajemen Culture</strong></div>
            <div style="font-weight: 600;">Halo, Admin</div>
        </header>

        <div class="content">
            <div class="header-flex">
                <h1>Daftar Kebiasaan (Culture)</h1>
                <button class="btn-add" onclick="openModal()">+ Tambah Culture</button>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Culture</th>
                            <th>Deskripsi</th>
                            <th>Frekuensi</th>
                            <th>Bobot</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-dark);"><?= $row['namaCulture'] ?></td>
                            <td style="color: #64748B; max-width: 300px;"><?= $row['deskripsi'] ?></td>
                            <td><span class="badge-freq"><?= $row['frekuensi'] ?></span></td>
                            <td style="font-weight: 700;"><?= $row['bobotNilai'] ?> Poin</td>
                            <td>
                                <a href="manage_cultures.php?delete=<?= $row['cultureID'] ?>" style="color: #EF4444; text-decoration: none; font-size: 13px;" onclick="return confirm('Hapus culture ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal" id="modalCulture">
        <div class="modal-box">
            <h3 style="margin-bottom: 20px;">Tambah Kebiasaan Baru</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Kebiasaan</label>
                    <input type="text" name="namaCulture" placeholder="Contoh: Doa Pagi" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Jelaskan detail aktivitas..."></textarea>
                </div>
                <div class="form-group">
                    <label>Frekuensi</label>
                    <select name="frekuensi">
                        <option value="Harian">Harian</option>
                        <option value="Mingguan">Mingguan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Bobot Poin</label>
                    <input type="number" step="0.1" name="bobotNilai" value="1.0">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="closeModal()" style="background: none; border: 1px solid #ddd; padding: 10px 20px; border-radius: 10px; cursor: pointer;">Batal</button>
                    <button type="submit" name="add_culture" class="btn-add">Simpan</button>
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
        function openModal() { document.getElementById('modalCulture').style.display = 'flex'; }
        function closeModal() { document.getElementById('modalCulture').style.display = 'none'; }
    </script>
</body>
</html>