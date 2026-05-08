<?php
session_start();
require_once '../config/database.php';

// Proteksi Login
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// Simulasi Data (Nantinya dihubungkan ke query COUNT & AVG dari checklist_log)
$kepatuhan_avg = "87%";
$total_habit_terisi = "2,345";
$user_teraktif = "Ahmad Rizki";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan & Statistik | Wesclic Habits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-green: #146332;
            --active-green: #22c55e;
            --sidebar-bg: var(--sidebar-green, #146332);
            --sidebar-active: var(--active-green, #22c55e);
            --bg-body: #F8FAFC;
            --white: #FFFFFF;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border: #E2E8F0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-body); display: flex; height: 100vh; overflow: hidden; }

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
        
        /* Filter Section */
        .filter-card { background: white; padding: 25px; border-radius: 20px; border: 1px solid var(--border); margin-bottom: 25px; display: flex; align-items: flex-end; gap: 20px; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; gap: 8px; }
        .filter-group label { font-size: 12px; font-weight: 700; color: var(--text-dark); }
        .filter-group input, .filter-group select { padding: 10px; border: 1px solid var(--border); border-radius: 10px; font-size: 13px; }
        .btn-filter { background: var(--active-green); color: white; border: none; padding: 10px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .btn-export { background: white; border: 1px solid var(--border); padding: 10px 20px; border-radius: 10px; cursor: pointer; font-size: 13px; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .stat-card h3 { font-size: 24px; font-weight: 700; }
        .stat-card p { font-size: 13px; color: var(--text-muted); margin-bottom: 5px; }

        /* Chart & Table */
        .chart-container { background: white; padding: 30px; border-radius: 24px; border: 1px solid var(--border); margin-bottom: 30px; }
        .table-section { background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: var(--active-green); color: white; padding: 16px 24px; text-align: left; font-size: 13px; }
        td { padding: 16px 24px; border-bottom: 1px solid #F1F5F9; font-size: 14px; }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M21.21 15.89A10 10 0 1 1 8 2.83M22 12A10 10 0 0 0 12 2v10z"></path></svg>
            <h2 class="brand-text">Habits Tracking</h2>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
            <a href="manage_users.php" class="nav-link"><span class="nav-text">Manajemen User</span></a>
            <a href="manage_cultures.php" class="nav-link"><span class="nav-text">Manajemen Culture</span></a>
            <a href="manage_file.php" class="nav-link"><span class="nav-text">Manajemen File</span></a>
            <a href="manage_notifications.php" class="nav-link"><span class="nav-text">Notifikasi</span></a>
            <a href="reports.php" class="nav-link active"><span class="nav-text">Laporan & Statistik</span></a>
            <a href="pengaturan.php" class="nav-link"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Logout</span></a>
        </nav>
        <div class="sidebar-footer" onclick="toggleSidebar()">
            <span id="collapse-btn">« Collapse</span>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <div style="font-size: 12px; color: var(--text-muted);">Dashboard > <strong>Laporan & Statistik</strong></div>
            <div style="font-weight: 600;">Halo, Admin</div>
        </header>

        <div class="content">
            <h1 style="font-size: 24px; margin-bottom: 5px;">Laporan & Statistik Global</h1>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 30px;">Pantau performa dan kepatuhan seluruh organisasi.</p>

            <div class="filter-card">
                <div class="filter-group">
                    <label>Rentang Tanggal</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="date" value="2022-11-25">
                        <span>-</span>
                        <input type="date" value="2022-11-25">
                    </div>
                </div>
                <div class="filter-group">
                    <label>Filter per Divisi</label>
                    <select style="min-width: 150px;">
                        <option>Semua Divisi</option>
                        <option>IT</option>
                        <option>HR</option>
                    </select>
                </div>
                <button class="btn-filter">Terapkan Filter</button>
                <button class="btn-export">📥 Ekspor Laporan</button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div>
                        <p>Kepatuhan Rata-rata</p>
                        <h3><?= $kepatuhan_avg ?></h3>
                        <small style="color: var(--active-green);">+5% dari minggu lalu</small>
                    </div>
                    <div style="color: var(--active-green);">📈</div>
                </div>
                <div class="stat-card">
                    <div>
                        <p>Total Habit Terisi</p>
                        <h3><?= $total_habit_terisi ?></h3>
                        <small style="color: var(--active-green);">+125 hari ini</small>
                    </div>
                    <div style="color: var(--active-green);">📈</div>
                </div>
                <div class="stat-card">
                    <div>
                        <p>User Paling Aktif</p>
                        <h3 style="font-size: 18px;"><?= $user_teraktif ?></h3>
                        <small style="color: var(--active-green);">158 poin minggu ini</small>
                    </div>
                    <div style="color: var(--active-green);">📈</div>
                </div>
            </div>

            <div class="chart-container">
                <h3 style="font-size: 18px; margin-bottom: 20px;">Trend Kepatuhan Mingguan</h3>
                <canvas id="trendChart" height="100"></canvas>
            </div>

            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Divisi</th>
                            <th>Jumlah Karyawan</th>
                            <th>Kepatuhan</th>
                            <th>Habit Terisi</th>
                            <th>User Terbaik</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $divisi_data = ['HR', 'Finance', 'Operations', 'Marketing', 'IT'];
                        foreach($divisi_data as $d): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= $d ?></td>
                            <td>25</td>
                            <td><span style="background: #DCFCE7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;">92%</span></td>
                            <td>487</td>
                            <td>Siti Nurhaliza</td>
                        </tr>
                        <?php endforeach; ?>
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

        // Chart.js Configuration
        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5', 'Minggu 6'],
                datasets: [{
                    label: 'Performa Aktual',
                    data: [65, 45, 20, 25, 30, 65],
                    backgroundColor: '#22c55e',
                    borderRadius: 10,
                    barThickness: 45
                }, {
                    label: 'Target',
                    data: [30, 55, 80, 75, 65, 30],
                    backgroundColor: '#BBF7D0',
                    borderRadius: 10,
                    barThickness: 45
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, max: 100 }
                },
                plugins: { legend: { position: 'top', align: 'end' } }
            }
        });
    </script>
</body>
</html>

