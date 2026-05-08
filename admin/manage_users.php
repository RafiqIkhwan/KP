<?php
session_start();
require_once '../config/database.php';

// Proteksi Login
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// Proses Hapus User
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE userID = $id");
    header("Location: manage_users.php?msg=success_delete");
    exit();
}

// Ambil Data User dengan Join
$sql = "SELECT u.userID, u.namaLengkap, u.email, u.role, u.statusAktif, 
        k.divisi, pm.timID, ad.departemen 
        FROM users u
        LEFT JOIN karyawan k ON u.userID = k.userID
        LEFT JOIN project_manager pm ON u.userID = pm.userID
        LEFT JOIN admin ad ON u.userID = ad.userID
        ORDER BY u.userID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User | Habits Tracking</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #146332;
            --sidebar-active: #22c55e;
            --main-bg: #F8FAFC;
            --white: #FFFFFF;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --danger: #ef4444;
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
        .top-nav { 
            height: 80px; background: var(--white); border-bottom: 1px solid var(--border-color); 
            display: flex; align-items: center; justify-content: space-between; padding: 0 40px; 
        }
        .top-nav .burger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .content { padding: 30px 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-add { 
            background: var(--sidebar-active); color: white; text-decoration: none; 
            padding: 12px 24px; border-radius: 30px; font-size: 14px; font-weight: 600; 
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2);
        }

        /* Table Design */
        .table-card { 
            background: var(--white); border-radius: 20px; border: 1px solid var(--border-color); 
            overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        table { width: 100%; border-collapse: collapse; }
        th { 
            background: #F8FAFC; padding: 16px 24px; text-align: left; 
            font-size: 12px; font-weight: 700; color: var(--text-muted); 
            text-transform: uppercase; border-bottom: 1px solid var(--border-color);
        }
        td { padding: 18px 24px; font-size: 14px; border-bottom: 1px solid #F1F5F9; color: var(--text-dark); }
        
        /* Badges */
        .badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; }
        .badge-admin { background: #DCFCE7; color: #166534; }
        .badge-pm { background: #FEF9C3; color: #854d0e; }
        .badge-karyawan { background: #DBEAFE; color: #1e40af; }

        .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        
        .action-btn { 
            text-decoration: none; font-size: 13px; font-weight: 600; margin-right: 15px; 
            transition: 0.2s; 
        }
        .edit-btn { color: var(--sidebar-active); }
        .delete-btn { color: var(--danger); }

        .alert { 
            padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; 
            font-size: 14px; font-weight: 500; border: 1px solid transparent;
        }
        .alert-success { background: #DCFCE7; color: #166534; border-color: #BBF7D0; }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M4 4v16M20 4v16M4 12h16"></path></svg>
            <h2>Habits Tracking<br>Culture Office</h2>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
            <a href="manage_users.php" class="nav-link active"><span class="nav-text">Manajemen User</span></a>
            <a href="manage_cultures.php" class="nav-link"><span class="nav-text">Manajemen Culture</span></a>
            <a href="manage_file.php" class="nav-link"><span class="nav-text">Manajemen File</span></a>
            <a href="manage_notifications.php" class="nav-link"><span class="nav-text">Notifikasi</span></a>
            <a href="reports.php" class="nav-link"><span class="nav-text">Laporan & Statistik</span></a>
            <a href="pengaturan.php" class="nav-link"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Logout</span></a>
        </nav>
        <div class="sidebar-footer" onclick="toggleSidebar()">
            <span id="collapse-btn">« Collapse</span>
            <p style="margin-top: 10px; opacity: 0.6;">Habits Tracking v1.0</p>
        </div>
    </aside>

    <main class="main-content">
        <nav class="top-nav">
            <div style="font-size: 13px; color: var(--text-muted);">Dashboard > <strong>Manajemen User</strong></div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="text-align: right;">
                    <p style="font-size: 13px; font-weight: 600;">Halo, Admin</p>
                </div>
                <div style="width: 35px; height: 35px; background: #E2E8F0; border-radius: 50%;"></div>
            </div>
        </nav>

        <div class="content">
            <div class="header-flex">
                <div>
                    <h1 style="font-size: 24px; color: var(--text-dark);">Daftar Pengguna</h1>
                    <p style="color: var(--text-muted); font-size: 14px;">Kelola akun Admin, Project Manager, dan Karyawan.</p>
                </div>
                <a href="../register.php" class="btn-add">+ Tambah User</a>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success_delete'): ?>
                <div class="alert alert-success">User berhasil dihapus dari sistem.</div>
            <?php endif; ?>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Profil Pengguna</th>
                            <th>Role</th>
                            <th>Detail Informasi</th>
                            <th>Status Akun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?= $user['namaLengkap'] ?></div>
                                <div style="font-size: 12px; color: var(--text-muted);"><?= $user['email'] ?></div>
                            </td>
                            <td>
                                <?php 
                                    $bClass = ($user['role'] == 'Admin') ? 'badge-admin' : (($user['role'] == 'ProjectManager') ? 'badge-pm' : 'badge-karyawan');
                                ?>
                                <span class="badge <?= $bClass ?>"><?= $user['role'] ?></span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 13px;">
                                <?php 
                                    if($user['role'] == 'Karyawan') echo "Divisi: " . ($user['divisi'] ?? 'N/A');
                                    elseif($user['role'] == 'ProjectManager') echo "Tim ID: " . ($user['timID'] ?? 'N/A');
                                    else echo "Dept: " . ($user['departemen'] ?? 'N/A');
                                ?>
                            </td>
                            <td>
                                <span class="status-dot" style="background: <?= $user['statusAktif'] ? '#22c55e' : '#ef4444' ?>;"></span>
                                <span style="font-size: 13px; font-weight: 500;"><?= $user['statusAktif'] ? 'Aktif' : 'Nonaktif' ?></span>
                            </td>
                            <td>
                                <a href="#" class="action-btn edit-btn">Edit</a>
                                <a href="manage_users.php?delete=<?= $user['userID'] ?>" 
                                   class="action-btn delete-btn" 
                                   onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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

