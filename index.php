<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['userID'])) {
    if ($_SESSION['role'] == 'Admin') header("Location: admin/manage_users.php");
    else if ($_SESSION['role'] == 'ProjectManager') header("Location: pm/dashboard_tim.php");
    else if ($_SESSION['role'] == 'Karyawan') header("Location: karyawan/dashboard.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; 
    $role = $conn->real_escape_string($_POST['role']);

    // Query mengecek kombinasi Email dan Role
    $sql = "SELECT userID, email, password, namaLengkap, role FROM users 
            WHERE email = '$email' AND role = '$role' AND statusAktif = 1 LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi Password 
        if ($password === $user['password']) {
            $_SESSION['userID']     = $user['userID'];
            $_SESSION['namaLengkap'] = $user['namaLengkap'];
            $_SESSION['role']        = $user['role'];

            // Routing Berdasarkan Role
            if ($user['role'] == 'Admin') header("Location: admin/manage_users.php");
            else if ($user['role'] == 'ProjectManager') header("Location: pm/dashboard_tim.php");
            else if ($user['role'] == 'Karyawan') header("Location: karyawan/dashboard.php");
            exit();
        } else {
            $error_message = "Password yang Anda masukkan salah.";
        }
    } else {
        $error_message = "Akun tidak ditemukan untuk role tersebut atau email salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | Habits Tracking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-wrapper {
            display: flex;
            width: 900px;
            height: 600px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        /* Bagian Kiri (Aset Grafis) */
        .graphic-side {
            flex: 1;
            /* Placeholder gradient mirip gambar Anda. Ganti dengan background-image aset asli Anda jika ada */
            background: linear-gradient(135deg, #22c55e, #84cc16, #16a34a);
            position: relative;
        }

        /* Bagian Kanan (Formulir) */
        .form-side {
            flex: 1.2;
            padding: 40px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo-container svg {
            width: 48px;
            height: 48px;
            color: #22c55e;
        }

        h2 {
            text-align: center;
            font-size: 28px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .signup-text {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 32px;
        }

        .signup-text a {
            color: #64748b;
            text-decoration: underline;
        }

        /* Styling Radio Buttons untuk Role */
        .role-selector {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .role-option {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #475569;
            cursor: pointer;
        }

        .role-option input[type="radio"] {
            margin-right: 8px;
            accent-color: #22c55e;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: #475569;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #22c55e;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .forgot-password {
            display: block;
            text-align: right;
            font-size: 12px;
            color: #64748b;
            margin-top: 8px;
            text-decoration: none;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: #22c55e;
            color: white;
            border: none;
            border-radius: 24px; /* Pill shape */
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 8px;
            transition: background-color 0.2s;
        }

        .btn-login:hover {
            background-color: #16a34a;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
            color: #94a3b8;
            font-size: 14px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider:not(:empty)::before {
            margin-right: .25em;
        }

        .divider:not(:empty)::after {
            margin-left: .25em;
        }

        .btn-google {
            width: 100%;
            padding: 12px;
            background-color: white;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .btn-google:hover {
            background-color: #f8fafc;
        }

        .alert {
            background-color: #fef2f2;
            color: #ef4444;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="graphic-side">
        </div>

    <div class="form-side">
        <div class="logo-container">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4v16M20 4v16M4 12h16"></path>
            </svg>
        </div>
        
        <h2>Sign in</h2>
        <div class="signup-text">
            Don't have an account? <a href="register.php">Sign up</a>
        </div>

        <?php if ($error_message): ?>
            <div class="alert"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="role-selector">
                <label class="role-option">
                    <input type="radio" name="role" value="Admin" required> Admin
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="ProjectManager" required> Project Manager
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="Karyawan" required checked> Karyawan
                </label>
            </div>

            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="passwordField" class="form-control" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <span id="toggleText">Hide</span>
                    </button>
                </div>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login">Login</button>

            <div class="divider">OR</div>

            <button type="button" class="btn-google">
                <svg width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path>
                </svg>
                Continue with Google
            </button>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById('passwordField');
        const toggleText = document.getElementById('toggleText');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleText.textContent = 'Show';
        } else {
            passwordField.type = 'password';
            toggleText.textContent = 'Hide';
        }
    }
</script>

</body>
</html>