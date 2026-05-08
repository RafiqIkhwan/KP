<?php
session_start();
require_once '../config/database.php';

// Proteksi Login
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// 1. Logika Kirim Notifikasi Baru
if (isset($_POST['send_notif'])) {
    $judul   = $conn->real_escape_string($_POST['judul']);
    $pesan   = $conn->real_escape_string($_POST['pesan']);
    $adminID = $_SESSION['userID'];

    $sql = "INSERT INTO notifikasi (judul, pesan, created_by, tanggal_kirim) 
            VALUES ('$judul', '$pesan', '$adminID', NOW())";
    
    if ($conn->query($sql)) {
        header("Location: manage_notifications.php?msg=sent");
    }
}

// 2. Logika Hapus Notifikasi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM notifikasi WHERE notifID = $id");
    header("Location: manage_notifications.php?msg=deleted");
}

// 3. Ambil Data Notifikasi & Pembuatnya
$result = $conn->query("SELECT n.*, u.namaLengkap FROM notifikasi n 
                        JOIN users u ON n.created_by = u.userID 
                        ORDER BY n.tanggal_kirim DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Notifikasi | Wesclic Habits</title>
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
            --text-dark: #1E293B;
            --text-muted: #64748B;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--main-bg); display: flex; height: 100vh; overflow: hidden; }

                /* Sidebar */
        .sidebar { width: 280px; background-color: var(--sidebar-bg); color: white; display: flex; flex-direction: column; padding: 30px 20px; transition: all 0.3s; }
        .sidebar.collapsed { width: 80px; padding: 30px 15px; }
        .sidebar.collapsed h2,
        .sidebar.collapsed .brand-text { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; }
        .sidebar.collapsed .nav-text { display: none; }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; }
        .brand h2 { font-size: 16px; font-weight: 700; line-height: 1.2; }

        .nav-menu { list-style: none; flex-grow: 1; }
        .nav-link { text-decoration: none; color: rgba(255,255,255,0.7); padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; gap: 12px; font-size: 14px; margin-bottom: 8px; transition: 0.3s; white-space: nowrap; }
        .nav-link.active { background-color: var(--sidebar-active); color: white; font-weight: 600; }
        .nav-link:hover:not(.active) { background-color: rgba(255,255,255,0.1); color: white; }

        .sidebar-footer { margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); cursor: pointer; font-size: 12px; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-nav { height: 80px; background: var(--white); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }
        
        .content { padding: 30px 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-create { background: var(--active-green); color: white; border: none; padding: 12px 24px; border-radius: 30px; font-weight: 600; cursor: pointer; font-size: 14px; }

        /* Table Style */
        .table-card { background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #F8FAFC; padding: 16px 24px; text-align: left; font-size: 12px; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 18px 24px; font-size: 14px; border-bottom: 1px solid #F1F5F9; }

        /* Modal */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 100; }
        .modal-box { background: white; padding: 32px; border-radius: 20px; width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark); }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; }
        
        .alert-success { background: #DCFCE7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; border: 1px solid #BBF7D0; }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <h2 class="brand-text">Habits Tracking</h2>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
            <a href="manage_users.php" class="nav-link"><span class="nav-text">Manajemen User</span></a>
            <a href="manage_cultures.php" class="nav-link"><span class="nav-text">Manajemen Culture</span></a>
            <a href="manage_file.php" class="nav-link"><span class="nav-text">Manajemen File</span></a>
            <a href="manage_notifications.php" class="nav-link active"><span class="nav-text">Notifikasi</span></a>
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
            <div style="font-size: 13px; color: var(--text-muted);">Dashboard > <strong>Notifikasi</strong></div>
            <div style="font-weight: 600;">Halo, Admin</div>
        </header>

        <div class="content">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'sent'): ?>
                <div class="alert-success">Notifikasi berhasil dikirim ke seluruh karyawan!</div>
            <?php endif; ?>

            <div class="header-flex">
                <div>
                    <h1>Pusat Notifikasi</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Kirim pengumuman penting kepada tim Anda.</p>
                </div>
                <button class="btn-create" onclick="openModal()">+ Buat Notifikasi</button>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Pengumuman</th>
                            <th>Pesan</th>
                            <th>Tanggal Kirim</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight: 600; width: 250px;"><?= $row['judul'] ?></td>
                            <td style="color: var(--text-muted); font-size: 13px;"><?= substr($row['pesan'], 0, 80) ?>...</td>
                            <td><?= date('d M Y, H:i', strtotime($row['tanggal_kirim'])) ?></td>
                            <td>
                                <a href="manage_notifications.php?delete=<?= $row['notifID'] ?>" style="color: #EF4444; text-decoration: none; font-weight: 600;" onclick="return confirm('Hapus riwayat notifikasi ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal" id="modalNotif">
        <div class="modal-box">
            <h3 style="margin-bottom: 20px; font-size: 18px;">Kirim Notifikasi Baru</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Subjek / Judul</label>
                    <input type="text" name="judul" placeholder="Contoh: Pengingat Update Progress" required>
                </div>
                <div class="form-group">
                    <label>Isi Pesan</label>
                    <textarea name="pesan" rows="5" placeholder="Tulis pesan lengkap di sini..." required></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;">
                    <button type="button" onclick="closeModal()" style="background: none; border: 1px solid var(--border); padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: 600;">Batal</button>
                    <button type="submit" name="send_notif" class="btn-create">Kirim Sekarang</button>
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
        function openModal() { document.getElementById('modalNotif').style.display = 'flex'; }
        function closeModal() { document.getElementById('modalNotif').style.display = 'none'; }
    </script>
</body>
</html>

