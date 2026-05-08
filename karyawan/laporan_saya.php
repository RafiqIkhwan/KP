<?php
session_start();
require_once '../config/database.php';

// Proteksi Login Karyawan
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Karyawan') {
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION['userID'];

// Simulasi Data Statistik (Nantinya diambil dari query COUNT & SUM checklist_log)
$stats = [
    'kehadiran' => '24 Hari',
    'habit_selesai' => '120 Misi',
    'streak' => '15 Hari',
    'skor' => '92%'
];

// Ambil Riwayat Aktivitas Terakhir
$sql_history = "SELECT cl.*, c.namaCulture, s.urlGambar 
                FROM checklist_log cl
                JOIN culture c ON cl.cultureID = c.cultureID
                LEFT JOIN swafoto s ON cl.logID = s.checklistID
                WHERE cl.userID = $userID 
                ORDER BY cl.tanggal DESC LIMIT 5";
$history_result = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Saya | Wesclic Habits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-green: #146332;
            --active-green: #22c55e;
            --bg-body: #F8FAFC;
            --white: #FFFFFF;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border: #E2E8F0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); display: flex; height: 100vh; overflow: hidden; }

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

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 16px; border: 1px solid var(--border); }
        .stat-card h3 { font-size: 20px; font-weight: 700; margin-top: 5px; }
        .stat-card p { font-size: 12px; color: var(--text-muted); }

        /* Section Two Columns */
        .performance-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px; }
        .card-box { background: white; padding: 30px; border-radius: 20px; border: 1px solid var(--border); }
        
        /* Bar Progress Mingguan */
        .weekly-row { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .progress-bg { flex: 1; height: 12px; background: #F1F5F9; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--active-green); border-radius: 10px; }

        /* Kalender Heatmap */
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; text-align: center; }
        .cal-day { height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 6px; font-size: 12px; font-weight: 600; border: 1px solid var(--border); }
        .cal-full { background: #22c55e; color: white; border: none; }
        .cal-partial { background: #BBF7D0; color: #166534; border: none; }
        .cal-empty { background: #FEE2E2; color: #991B1B; border: none; }

        /* Table Riwayat */
        .table-section { background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--active-green); color: white; padding: 16px 24px; text-align: left; font-size: 13px; }
        td { padding: 16px 24px; border-bottom: 1px solid #F1F5F9; font-size: 14px; }
        
        .status-badge { padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 700; }
        .status-selesai { background: #DCFCE7; color: #166534; }
        .status-ditolak { background: #FEE2E2; color: #991B1B; }
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
            <a href="laporan_saya.php" class="nav-link active"><span class="nav-text">Laporan Saya</span></a>
            <a href="file_center.php" class="nav-link "><span class="nav-text">File Center</span></a>
            <a href="settings.php" class="nav-link "><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Logout</span></a>
        </nav>
        <div class="sidebar-footer" onclick="toggleSidebar()"><span id="collapse-btn">« Collapse</span></div>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <div style="font-size: 12px; color: var(--text-muted);">Dashboard > <strong>Laporan Saya</strong></div>
            <div style="font-weight: 600;">Halo, <?= explode(' ', $_SESSION['namaLengkap'])[0] ?></div>
        </header>

        <div class="content">
            <h1 style="font-size: 24px; margin-bottom: 5px;">Statistik Kinerja Saya</h1>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">Pantau perkembangan dan konsistensi budaya kerja Anda</p>

            <div class="stats-grid">
                <div class="stat-card"><p>Total Kehadiran</p><h3><?= $stats['kehadiran'] ?></h3><small style="color: var(--active-green);">dari 26 hari kerja</small></div>
                <div class="stat-card"><p>Habit Selesai</p><h3><?= $stats['habit_selesai'] ?></h3><small style="color: var(--active-green);">bulan ini</small></div>
                <div class="stat-card"><p>Streak Terpanjang</p><h3><?= $stats['streak'] ?></h3><small style="color: var(--active-green);">konsisten</small></div>
                <div class="stat-card"><p>Skor Rata-rata</p><h3><?= $stats['skor'] ?></h3><small style="color: var(--active-green);">performa</small></div>
            </div>

            <div class="performance-grid">
                <div class="card-box">
                    <h3 style="font-size: 16px; margin-bottom: 25px;">Skor Kinerja Mingguan (Nov 2025)</h3>
                    <?php 
                        $progres = [65, 78, 72, 86, 90];
                        foreach($progres as $index => $val): 
                    ?>
                    <div class="weekly-row">
                        <span style="font-size: 13px; color: var(--text-muted); width: 50px;">Mgg <?= $index+1 ?></span>
                        <div class="progress-bg"><div class="progress-fill" style="width: <?= $val ?>%;"></div></div>
                        <span style="font-size: 13px; font-weight: 600;"><?= $val ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-box">
                    <h3 style="font-size: 16px; margin-bottom: 25px;">Konsistensi Saya</h3>
                    <div class="calendar-grid">
                        <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                        <?php for($i=1; $i<=30; $i++): 
                            $class = ($i % 7 == 0) ? 'cal-empty' : (($i % 3 == 0) ? 'cal-partial' : 'cal-full');
                        ?>
                            <div class="cal-day <?= $class ?>"><?= $i ?></div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="table-section">
                <table>
                    <thead>
                        <tr><th>Tanggal</th><th>Nama Habit</th><th>Bukti</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while($row = $history_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d F Y', strtotime($row['tanggal'])) ?></td>
                            <td style="font-weight: 600;"><?= $row['namaCulture'] ?></td>
                            <td><a href="../uploads/swafoto/<?= $row['urlGambar'] ?>" target="_blank" style="color: #4285F4; text-decoration: none;">Foto</a></td>
                            <td><span class="status-badge status-selesai">Selesai</span></td>
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