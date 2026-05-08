<?php
session_start();
require_once '../config/database.php';

// Proteksi Login
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// Simulasi Data Statistik (Nantinya diganti dengan Query SQL)
$totalKaryawan = 342; 
$progressHariIni = 87;
$cekMingguan = 94;
$totalHabit = 1250;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Habits Tracking</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-bg: #146332;
            --sidebar-active: #22c55e;
            --main-bg: #F8FAFC;
            --white: #FFFFFF;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
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
            display: flex; align-items: center; justify-content: space-between; padding: 0 40px; gap: 20px;
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
        .breadcrumb { font-size: 12px; color: var(--text-muted); white-space: nowrap; }
        .search-container { flex: 1; min-width: 0; }
        .search-container input { 
            width: 100%; max-width: 420px; padding: 10px 20px; border-radius: 30px; 
            border: 1px solid var(--border-color); background: #F1F5F9; font-size: 13px;
        }

        .user-profile { display: flex; align-items: center; gap: 15px; }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }

        /* Content Body */
        .content { padding: 30px 40px; }
        .header-text h1 { font-size: 24px; font-weight: 700; color: var(--text-dark); }
        .header-text p { color: var(--text-muted); margin-top: 5px; font-size: 14px; }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 30px 0; }
        .stat-card { 
            background: var(--white); padding: 25px; border-radius: 20px; 
            border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;
        }
        .stat-info p { font-size: 13px; color: var(--text-muted); margin-bottom: 8px; }
        .stat-info h3 { font-size: 24px; font-weight: 700; }
        .stat-info .trend { font-size: 11px; color: var(--sidebar-active); font-weight: 600; margin-top: 5px; }
        .stat-icon { width: 45px; height: 45px; background: #F0FDF4; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--sidebar-active); }

        /* Chart Section */
        .chart-box { 
            background: var(--white); padding: 30px; border-radius: 24px; 
            border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .chart-legend { display: flex; gap: 20px; font-size: 13px; color: var(--text-muted); }
        .legend-item { display: flex; align-items: center; gap: 8px; }
        .dot { width: 10px; height: 10px; border-radius: 50%; }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M4 4v16M20 4v16M4 12h16"></path></svg>
            <h2>Habits Tracking<br>Culture Office</h2>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-link active"><span class="nav-text">Dashboard</span></a>
            <a href="manage_users.php" class="nav-link"><span class="nav-text">Manajemen User</span></a>
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
            <div class="breadcrumb">Dashboard > <strong>Dashboard</strong></div>
            <div class="search-container"><input type="text" placeholder="Cari di sini..."></div>
            <div class="user-profile">
                <div style="text-align: right;">
                    <p style="font-size: 13px; font-weight: 600;">Halo, Admin</p>
                    <p style="font-size: 11px; color: var(--text-muted);">Selamat datang</p>
                </div>
                <img src="../assets/img/admin-avatar.jpg" alt="Profile">
            </div>
        </nav>

        <div class="content">
            <div class="header-text">
                <h1>Dashboard</h1>
                <p>Selamat datang kembali, Admin!</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <p>Total Karyawan</p>
                        <h3><?= $totalKaryawan ?></h3>
                        <div class="trend">+12% dari bulan lalu</div>
                    </div>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <p>Progress Hari Ini</p>
                        <h3><?= $progressHariIni ?>%</h3>
                        <div class="trend">+5% dari kemarin</div>
                    </div>
                    <div class="stat-icon">📈</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <p>Cek Mingguan</p>
                        <h3><?= $cekMingguan ?>%</h3>
                        <div class="trend">+2% dari minggu lalu</div>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <p>Total Habit</p>
                        <h3><?= $totalHabit ?></h3>
                        <div class="trend">+45 habit baru</div>
                    </div>
                    <div class="stat-icon">😊</div>
                </div>
            </div>

            <div class="chart-box">
                <div class="chart-header">
                    <div>
                        <p style="font-size: 12px; color: var(--text-muted);">Statistics</p>
                        <h2 style="font-size: 18px;">Grafik Performa Divisi</h2>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item"><span class="dot" style="background: #22c55e;"></span> Performa Aktual</div>
                        <div class="legend-item"><span class="dot" style="background: #BBF7D0;"></span> Target</div>
                        <select style="border: none; background: #F1F5F9; padding: 8px 15px; border-radius: 10px; font-size: 12px;">
                            <option>Last 6 months</option>
                        </select>
                    </div>
                </div>
                <canvas id="performaChart" height="100"></canvas>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('performaChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['HR', 'Finance', 'Operation', 'Marketing', 'IT', 'Sales'],
                datasets: [{
                    label: 'Performa Aktual',
                    data: [65, 45, 20, 25, 30, 65],
                    backgroundColor: '#22c55e',
                    borderRadius: 10,
                    barThickness: 40
                }, {
                    label: 'Target',
                    data: [30, 55, 80, 75, 65, 30],
                    backgroundColor: '#BBF7D0',
                    borderRadius: 10,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, max: 100, ticks: { stepSize: 25 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
    <script>
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const btn = document.getElementById('collapse-btn');
            sb.classList.toggle('collapsed');
            btn.innerHTML = sb.classList.contains('collapsed') ? '�' : '� Collapse';
        }
    </script>
</body>
</html>


