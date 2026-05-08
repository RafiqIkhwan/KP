<?php
session_start();
require_once '../config/database.php';

// Proteksi Login Karyawan
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Karyawan') {
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION['userID'];
$success_msg = "";
$error_msg = "";

// 1. Ambil Data Karyawan
$query = $conn->query("SELECT * FROM users WHERE userID = $userID");
$userData = $query->fetch_assoc();

// 2. Logika Update
if (isset($_POST['save_settings'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $passLama = $_POST['password_lama'];
    $passBaru = $_POST['password_baru'];
    $passKonf = $_POST['konfirmasi_password'];

    // Update profil dasar
    if (empty($passBaru)) {
        $conn->query("UPDATE users SET username = '$username' WHERE userID = $userID");
        $success_msg = "Profil berhasil diperbarui.";
    } else {
        // Validasi Password (Sederhana: ganti dengan password_verify jika pakai hash)
        if ($passLama !== $userData['password']) {
            $error_msg = "Password lama salah.";
        } elseif ($passBaru !== $passKonf) {
            $error_msg = "Konfirmasi password tidak cocok.";
        } else {
            $conn->query("UPDATE users SET username = '$username', password = '$passBaru' WHERE userID = $userID");
            $success_msg = "Password berhasil diperbarui.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Saya | Wesclic Habits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-green: #146332;
            --active-green: #22c55e;
            --main-bg: #F8FAFC;
            --white: #FFFFFF;
            --border: #E2E8F0;
            --text-muted: #64748B;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--main-bg); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar Collapse Style */
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

        /* Settings Card */
        .settings-card { background: white; border-radius: 24px; border: 1px solid var(--border); padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        .tabs { display: flex; gap: 10px; background: #F1F5F9; padding: 8px; border-radius: 15px; margin-bottom: 30px; width: fit-content; }
        .tab-item { padding: 10px 25px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; color: var(--text-muted); border: none; }
        .tab-item.active { background: white; color: var(--text-dark); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        .profile-header { display: flex; align-items: center; gap: 25px; padding-bottom: 30px; border-bottom: 1px solid var(--border); margin-bottom: 30px; }
        .avatar-box { width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid var(--active-green); }
        .avatar-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
        .form-group label { font-size: 14px; font-weight: 600; color: #334155; }
        .form-group input, .form-group select { padding: 12px 16px; border: 1px solid var(--border); border-radius: 12px; font-size: 14px; }
        
        .btn-save { background: var(--active-green); color: white; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; margin-top: 10px; }
        .btn-outline { background: white; border: 1px solid var(--border); padding: 8px 20px; border-radius: 10px; font-size: 13px; cursor: pointer; margin-top: 10px; }
        
        .msg { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; }
        .success { background: #DCFCE7; color: #166534; }
        .error { background: #FEE2E2; color: #991B1B; }
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
            <a href="file_center.php" class="nav-link"><span class="nav-text">File Center</span></a>
            <a href="settings.php" class="nav-link active"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Logout</span></a>
        </nav>
        <div class="sidebar-footer" onclick="toggleSidebar()"><span id="collapse-btn">« Collapse</span></div>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <div style="font-size: 12px; color: var(--text-muted);">Dashboard > <strong>Pengaturan</strong></div>
            <div style="font-weight: 600;">Halo, <?= explode(' ', $_SESSION['namaLengkap'])[0] ?></div>
        </header>

        <div class="content">
            <h1 style="font-size: 24px; margin-bottom: 5px;">Pengaturan Sistem</h1>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">Kelola konfigurasi sistem dan keamanan aplikasi</p>

            <?php if($success_msg): ?><div class="msg success"><?= $success_msg ?></div><?php endif; ?>
            <?php if($error_msg): ?><div class="msg error"><?= $error_msg ?></div><?php endif; ?>

            <div class="settings-card">
                <div class="tabs">
                    <button class="tab-item active">Profil Saya</button>
                    <button class="tab-item">Umum</button>
                    <button class="tab-item">Keamanan</button>
                </div>

                <div class="profile-header">
                    <div class="avatar-box">
                        <img src="../assets/img/foto.jpg" alt="Avatar">
                    </div>
                    <div>
                        <h3 style="font-size: 18px;"><?= $_SESSION['namaLengkap'] ?></h3>
                        <p style="font-size: 13px; color: var(--text-muted);"><?= $userData['email'] ?></p>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn-outline">📸 Ubah foto</button>
                            <button class="btn-outline" style="color: #EF4444;">🗑️ Hapus</button>
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= $userData['username'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= $userData['email'] ?>" disabled style="background: #F8FAFC; color: #94A3B8;">
                        <small style="color: #EF4444; font-size: 11px;">Email tidak dapat diubah</small>
                    </div>
                    <div class="form-group">
                        <label>🌐 Ganti Bahasa</label>
                        <select><option>Indonesia</option><option>English</option></select>
                    </div>
                    <div class="form-group">
                        <label>☀️ Tema Tampilan</label>
                        <select><option>Terang</option><option>Gelap</option></select>
                    </div>

                    <div style="margin: 30px 0; border-bottom: 1px solid var(--border);"></div>

                    <h4 style="margin-bottom: 20px; font-size: 15px;">🔒 Ganti Password</h4>
                    <div class="form-group"><label>Password Lama</label><input type="password" name="password_lama" placeholder="Masukan Password Lama"></div>
                    <div class="form-group"><label>Password Baru</label><input type="password" name="password_baru" placeholder="Masukan Password Baru"></div>
                    <div class="form-group"><label>Konfirmasi Password Baru</label><input type="password" name="konfirmasi_password" placeholder="Konfirmasi Password Baru"></div>

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