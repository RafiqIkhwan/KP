<?php
session_start();
require_once '../config/database.php';

// Proteksi Login Karyawan
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Karyawan') {
    header("Location: ../index.php");
    exit();
}

$userID = $_SESSION['userID'];

// 1. Ambil Data Statistik Karyawan
$stats = [
    'progress' => '1/6',
    'streak' => 12,
    'total_poin' => 450
];

// 2. Ambil Daftar Misi Hari Ini (Culture)
$query_misi = "SELECT * FROM culture WHERE statusAktif = 1";
$misi_result = $conn->query($query_misi);

// 3. Logika Simpan Laporan (Checklist + Swafoto)
if (isset($_POST['simpan_laporan'])) {
    $cultureID = $_POST['cultureID'];
    $catatan = $conn->real_escape_string($_POST['catatan']);
    
    // Proses Upload Foto
    $targetDir = "../uploads/swafoto/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    $fileName = "BUKTI_" . time() . "_" . basename($_FILES["bukti_foto"]["name"]);
    $targetPath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["bukti_foto"]["tmp_name"], $targetPath)) {
        // Simpan ke ChecklistLog (Sesuai Class Diagram)
        $sql_log = "INSERT INTO checklist_log (userID, cultureID, tanggal, status, catatanRefleksi, waktuSubmit) 
                    VALUES ('$userID', '$cultureID', CURDATE(), 1, '$catatan', NOW())";
        
        if ($conn->query($sql_log)) {
            $logID = $conn->insert_id;
            // Simpan ke tabel Swafoto sebagai bukti
            $conn->query("INSERT INTO swafoto (checklistID, urlGambar, timestamp) VALUES ('$logID', '$fileName', NOW())");
            header("Location: dashboard.php?msg=success");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Karyawan | Wesclic Habits</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-green: #146332;
            --active-green: #22c55e;
            --main-bg: #F1F3F6;
            --white: #FFFFFF;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --border: #E2E8F0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--main-bg); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar Collapse */
        .sidebar { width: 280px; background-color: var(--sidebar-green); color: white; display: flex; flex-direction: column; padding: 30px 20px; transition: 0.3s; }
        .sidebar.collapsed { width: 80px; padding: 30px 15px; }
        .sidebar.collapsed .nav-text, .sidebar.collapsed .brand-text { display: none; }
        .nav-link { text-decoration: none; color: rgba(255,255,255,0.7); padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; gap: 12px; font-size: 14px; margin-bottom: 8px; transition: 0.3s; }
        .nav-link.active { background-color: var(--active-green); color: white; font-weight: 600; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-nav { height: 80px; background: var(--white); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }

        .content { padding: 30px 40px; }
        .header-text h1 { font-size: 24px; color: var(--text-dark); }
        .header-text p { color: var(--text-muted); font-size: 14px; margin-top: 5px; }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 30px 0; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; border: 1px solid var(--border); position: relative; }
        .stat-card h3 { font-size: 22px; font-weight: 700; }
        .stat-card p { font-size: 13px; color: var(--text-muted); margin-bottom: 5px; }

        /* Checklist Table */
        .table-section { background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; }
        .table-header { background: var(--active-green); color: white; padding: 15px 25px; font-weight: 700; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 18px 25px; border-bottom: 1px solid #F1F5F9; }
        .btn-lapor { background: #86efac; color: #166534; border: none; padding: 8px 25px; border-radius: 20px; font-weight: 700; cursor: pointer; font-size: 13px; }
        .btn-terkirim { background: #f1f5f9; color: #94a3b8; cursor: default; }

        /* Modal Style */
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-box { background: white; padding: 35px; border-radius: 24px; width: 550px; }
        .upload-area { border: 2px dashed #cbd5e1; border-radius: 15px; padding: 40px; text-align: center; color: var(--text-muted); cursor: pointer; margin-bottom: 20px; }
        .btn-save { background: var(--active-green); color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M4 4v16M20 4v16M4 12h16"></path></svg>
            <h2 class="brand-text">Habits Tracking</h2>
        </div>
        <nav class="nav-menu">
           <a href="dashboard.php" class="nav-link active"><span class="nav-text">Dashboard</span></a>
            <a href="laporan_saya.php" class="nav-link"><span class="nav-text">Laporan Saya</span></a>
            <a href="file_center.php" class="nav-link"><span class="nav-text">File Center</span></a>
            <a href="settings.php" class="nav-link"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Logout</span></a>
        </nav>
        <div class="sidebar-footer" onclick="toggleSidebar()"><span id="collapse-btn">« Collapse</span></div>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <div style="font-size: 12px; color: var(--text-muted);">Dashboard > <strong>Dashboard</strong></div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="text-align: right;"><p style="font-size: 13px; font-weight: 600;">Halo, <?= $_SESSION['namaLengkap'] ?></p><p style="font-size: 11px; color: var(--text-muted);">Karyawan</p></div>
                <div style="width: 40px; height: 40px; background: #ddd; border-radius: 50%;"></div>
            </div>
        </header>

        <div class="content">
            <div class="header-text">
                <h1>Dashboard</h1>
                <p>Selamat pagi, <?= explode(' ', $_SESSION['namaLengkap'])[0] ?>! Awali harimu dengan menuntaskan habit harian.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card"><p>Progress Harian</p><h3><?= $stats['progress'] ?></h3><small style="color: var(--active-green);">selesai hari ini</small></div>
                <div class="stat-card"><p>Hari Beruntun</p><h3><?= $stats['streak'] ?></h3><small style="color: var(--active-green);">streak dipertahankan</small></div>
                <div class="stat-card"><p>Total Poin</p><h3><?= $stats['total_poin'] ?></h3><small style="color: var(--active-green);">poin terkumpul</small></div>
            </div>

            <div class="table-section">
                <div class="table-header">Misi Budaya Hari Ini</div>
                <table>
                    <tbody>
                        <?php while($misi = $misi_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--text-dark);"><?= $misi['namaCulture'] ?></div>
                                <div style="font-size: 12px; color: var(--text-muted);"><?= date('l, d M Y') ?> • 07.00 - 08.00</div>
                            </td>
                            <td style="text-align: right;">
                                <button class="btn-lapor" onclick="openModal(<?= $misi['cultureID'] ?>, '<?= $misi['namaCulture'] ?>')">Lapor</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal" id="modalLapor">
        <div class="modal-box">
            <h2 id="modalTitle" style="font-size: 18px; margin-bottom: 20px;">Laporan: </h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="cultureID" id="inputCultureID">
                
                <label style="font-size: 13px; font-weight: 700;">Upload Bukti Foto (Wajib)</label>
                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                    <p style="font-size: 13px; margin-top: 10px;">Seret foto ke sini atau klik untuk memilih</p>
                    <input type="file" name="bukti_foto" id="fileInput" style="display: none;" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 13px; font-weight: 700;">Catatan / Refleksi (Opsional)</label>
                    <textarea name="catatan" rows="4" style="width: 100%; border: 1px solid var(--border); border-radius: 12px; padding: 12px; margin-top: 8px;" placeholder="Tuliskan catatan atau refleksi anda."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" onclick="closeModal()" style="background: none; border: 1px solid var(--active-green); color: var(--active-green); padding: 10px 25px; border-radius: 10px; font-weight: 700; cursor: pointer;">Batal</button>
                    <button type="submit" name="simpan_laporan" class="btn-save">Simpan</button>
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
        function openModal(id, nama) {
            document.getElementById('inputCultureID').value = id;
            document.getElementById('modalTitle').innerHTML = "Laporan: " + nama;
            document.getElementById('modalLapor').style.display = 'flex';
        }
        function closeModal() { document.getElementById('modalLapor').style.display = 'none'; }
    </script>
</body>
</html>