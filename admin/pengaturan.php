<?php
session_start();
require_once '../config/database.php';

// Proteksi Login
if (!isset($_SESSION['userID'])) {
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION['userID'];
$success_msg = "";
$error_msg = "";

// 1. Ambil Data User Terbaru
$query = $conn->query("SELECT * FROM users WHERE userID = $userID");
$userData = $query->fetch_assoc();

// 2. Logika Update Profil & Password
if (isset($_POST['save_settings'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $passLama = $_POST['password_lama'];
    $passBaru = $_POST['password_baru'];
    $passKonf = $_POST['konfirmasi_password'];

    // Update Username saja jika password kosong
    if (empty($passBaru)) {
        $conn->query("UPDATE users SET username = '$username' WHERE userID = $userID");
        $success_msg = "Pengaturan profil berhasil diperbarui.";
    } else {
        // Cek Password Lama (Ganti dengan password_verify jika menggunakan hashing)
        if ($passLama !== $userData['password']) {
            $error_msg = "Password lama salah.";
        } elseif ($passBaru !== $passKonf) {
            $error_msg = "Konfirmasi password baru tidak cocok.";
        } else {
            $conn->query("UPDATE users SET username = '$username', password = '$passBaru' WHERE userID = $userID");
            $success_msg = "Profil dan Password berhasil diperbarui.";
        }
    }
    // Refresh data
    header("Location: pengaturan.php?status=success");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Sistem | Wesclic Habits</title>
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

        /* Settings Card */
        .settings-card { background: white; border-radius: 24px; border: 1px solid var(--border); padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        /* Tab Menu */
        .tabs { display: flex; gap: 10px; background: #F1F5F9; padding: 8px; border-radius: 15px; margin-bottom: 30px; width: fit-content; }
        .tab-item { padding: 10px 25px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; color: var(--text-muted); border: none; }
        .tab-item.active { background: white; color: var(--text-dark); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        /* Profile Header */
        .profile-header { display: flex; align-items: center; gap: 25px; padding-bottom: 30px; border-bottom: 1px solid var(--border); margin-bottom: 30px; }
        .avatar-box { width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid var(--active-green); }
        .avatar-box img { width: 100%; height: 100%; object-fit: cover; }
        .profile-info h3 { font-size: 18px; margin-bottom: 5px; }
        .profile-info p { font-size: 13px; color: var(--text-muted); }

        /* Form Styling */
        .form-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 600; color: #334155; }
        .form-group input, .form-group select { padding: 12px 16px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; outline: none; }
        .form-group input:focus { border-color: var(--active-green); }
        
        .btn-save { background: var(--active-green); color: white; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; width: fit-content; margin-top: 20px; }
        .btn-outline { background: white; border: 1px solid var(--border); padding: 8px 20px; border-radius: 10px; font-size: 13px; cursor: pointer; }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            <h2 class="brand-text">Habits Tracking</h2>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
            <a href="manage_users.php" class="nav-link"><span class="nav-text">Manajemen User</span></a>
            <a href="manage_cultures.php" class="nav-link"><span class="nav-text">Manajemen Culture</span></a>
            <a href="manage_file.php" class="nav-link"><span class="nav-text">Manajemen File</span></a>
            <a href="manage_notifications.php" class="nav-link"><span class="nav-text">Notifikasi</span></a>
            <a href="reports.php" class="nav-link"><span class="nav-text">Laporan & Statistik</span></a>
            <a href="pengaturan.php" class="nav-link active"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Logout</span></a>
        </nav>
        <div class="sidebar-footer" onclick="toggleSidebar()">
            <span id="collapse-btn">« Collapse</span>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <div style="font-size: 12px; color: var(--text-muted);">Dashboard > <strong>Pengaturan</strong></div>
            <div style="font-weight: 600;">Halo, Admin</div>
        </header>

        <div class="content">
            <h1 style="font-size: 24px; margin-bottom: 5px;">Pengaturan Sistem</h1>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">Kelola konfigurasi sistem dan keamanan aplikasi</p>

            <div class="settings-card">
                <div class="tabs">
                    <button class="tab-item active">Profil Saya</button>
                    <button class="tab-item">Umum</button>
                    <button class="tab-item">Keamanan</button>
                    <button class="tab-item">Integrasi</button>
                </div>

                <div class="profile-header">
                    <div class="avatar-box">
                        <img src="../assets/img/admin-avatar.jpg" alt="Admin Avatar">
                    </div>
                    <div class="profile-info">
                        <h3>User Admin</h3>
                        <p><?= $userData['email'] ?></p>
                        <div style="margin-top: 10px; display: flex; gap: 10px;">
                            <button class="btn-outline">📸 Ubah foto</button>
                            <button class="btn-outline" style="color: #EF4444;">🗑️ Hapus</button>
                        </div>
                    </div>
                </div>

                <form method="POST" class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= $userData['username'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= $userData['email'] ?>" disabled style="background: #F8FAFC;">
                        <small style="color: #EF4444; font-size: 11px;">Email tidak dapat diubah</small>
                    </div>
                    <div class="form-group">
                        <label>🌐 Ganti Bahasa</label>
                        <select>
                            <option>Indonesia</option>
                            <option>English</option>
                        </select>
                    </div>

                    <div style="margin: 20px 0; border-bottom: 1px solid var(--border);"></div>

                    <h4 style="font-size: 15px; margin-bottom: 10px;">🔒 Ganti Password</h4>
                    <div class="form-group">
                        <label>Password Lama</label>
                        <input type="password" name="password_lama" placeholder="Masukan Password Lama">
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password_baru" placeholder="Masukan Password Baru">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi_password" placeholder="Konfirmasi Password Baru">
                    </div>

                    <button type="submit" name="save_settings" class="btn-save">Simpan Semua Pengaturan</button>
                </form>
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

