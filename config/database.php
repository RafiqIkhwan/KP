<?php
// config/database.php

// Deklarasi parameter koneksi database
$host       = "localhost";      // Server database (biasanya localhost untuk XAMPP/Laragon)
$username   = "root";           // Username default MySQL
$password   = "";               // Password default MySQL (kosongkan jika menggunakan XAMPP)
$dbname     = "db_habits_culture";      // Nama database yang akan kita buat (sesuaikan jika berbeda)

// Membuat koneksi ke database menggunakan MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Mengecek apakah koneksi berhasil
if ($conn->connect_error) {
    // Jika gagal, hentikan eksekusi script dan tampilkan pesan error
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Opsional: Mengatur charset ke utf8mb4 agar mendukung karakter khusus (seperti emoji)
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
}
?>