<?php
session_start();
require_once 'config/database.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $namaLengkap = $conn->real_escape_string($_POST['namaLengkap']);
    $email       = $conn->real_escape_string($_POST['email']);
    $password    = $_POST['password']; 
    $role        = $_POST['role'];
    $username    = explode('@', $email)[0]; // Otomatis buat username dari email

    // Cek apakah email sudah terdaftar
    $checkEmail = $conn->query("SELECT email FROM users WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $error_message = "Email sudah terdaftar. Silakan gunakan email lain.";
    } else {
        // 1. Insert ke tabel induk: users
        $sql_user = "INSERT INTO users (username, password, namaLengkap, email, role, tanggalBergabung, statusAktif) 
                     VALUES ('$username', '$password', '$namaLengkap', '$email', '$role', CURDATE(), 1)";

        if ($conn->query($sql_user) === TRUE) {
            $last_id = $conn->insert_id; // Mengambil userID yang baru saja digenerate

            // 2. Insert ke tabel spesifik berdasarkan Role agar tidak melanggar Foreign Key
            if ($role == 'Admin') {
                $conn->query("INSERT INTO admin (userID, levelAkses, departemen) VALUES ('$last_id', 'Staff', 'General')");
            } else if ($role == 'ProjectManager') {
                $conn->query("INSERT INTO project_manager (userID, timID, jumlahAnggota) VALUES ('$last_id', 0, 0)");
            } else {
                $conn->query("INSERT INTO karyawan (userID, divisi, jabatan) VALUES ('$last_id', 'Unassigned', 'Staff')");
            }

            $success_message = "Akun berhasil dibuat! Silakan <a href='index.php' style='color: #22c55e; font-weight:600;'>Login di sini</a>";
        } else {
            $error_message = "Terjadi kesalahan sistem: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up | Habits Tracking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; }
        
        .auth-wrapper { 
            display: flex; width: 900px; height: 650px; background: #fff; 
            border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            overflow: hidden; border: 1px solid #e2e8f0; 
        }

        .graphic-side { 
            flex: 1; background: linear-gradient(135deg, #22c55e, #84cc16, #16a34a); 
        }

        .form-side { 
            flex: 1.2; padding: 40px 60px; display: flex; flex-direction: column; justify-content: center; 
            overflow-y: auto;
        }

        .logo-container { text-align: center; margin-bottom: 20px; color: #22c55e; }
        h2 { text-align: center; font-size: 26px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
        .signin-text { text-align: center; font-size: 14px; color: #64748b; margin-bottom: 24px; }
        .signin-text a { color: #64748b; text-decoration: underline; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; color: #475569; margin-bottom: 6px; font-weight: 500; }
        .form-control { 
            width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; 
            border-radius: 8px; font-size: 14px; outline: none; 
        }
        .form-control:focus { border-color: #22c55e; }

        .btn-register { 
            width: 100%; padding: 12px; background-color: #22c55e; color: white; 
            border: none; border-radius: 24px; font-size: 15px; font-weight: 500; 
            cursor: pointer; margin-top: 10px; transition: 0.2s; 
        }
        .btn-register:hover { background-color: #16a34a; }

        .alert { 
            padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center; 
        }
        .alert-error { background-color: #fef2f2; color: #ef4444; }
        .alert-success { background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="graphic-side"></div>

    <div class="form-side">
        <div class="logo-container">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v16M20 4v16M4 12h16"></path></svg>
        </div>
        
        <h2>Create Account</h2>
        <div class="signin-text">Already have an account? <a href="index.php">Sign in</a></div>

        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="namaLengkap" class="form-control" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" class="form-control" placeholder="name@wesclic.com" required>
            </div>

            <div class="form-group">
                <label>Register as</label>
                <select name="role" class="form-control" required>
                    <option value="Karyawan">Karyawan</option>
                    <option value="ProjectManager">Project Manager</option>
                    <option value="Admin">Admin (HR)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Create a password" required>
            </div>

            <button type="submit" class="btn-register">Sign Up</button>
        </form>
    </div>
</div>

</body>
</html>