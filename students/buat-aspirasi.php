<?php
session_start();
include('../config/connection.php');

// Pastikan user sudah login dan rolenya student
if (!isset($_SESSION['id_student']) || $_SESSION['role'] != 'student') {
  header("Location: ../logreg/login.php");
  exit;
}

// Ambil ID student dari session
$id_student = $_SESSION['id_student'];

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $title = mysqli_real_escape_string($conn, $_POST['title']);
  $content = mysqli_real_escape_string($conn, $_POST['content']);

  // Simpan ke database
  $query = "INSERT INTO aspirations (id_student, title, content, status)
            VALUES ('$id_student', '$title', '$content', 'Pending')";

  if (mysqli_query($conn, $query)) {
    echo "<script>
            alert('Aspirasi berhasil dikirim!');
            window.location.href = 'dashboard.php';
          </script>";
  } else {
    echo "<script>alert('Gagal mengirim aspirasi: " . mysqli_error($conn) . "');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Aspirasi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-bullhorn"></i>
                <h2>Suara Siswa</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="active"><a href="buat-aspirasi.php"><i class="fas fa-plus-circle"></i>Buat Aspirasi</a></li>
                <li><a href="../profile/edit-profile.php"><i class="fas fa-user"></i> Profil Saya</a></li>
                <li><a href="../logreg/login.html"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h3>Buat Aspirasi</h3>
                <div class="profile">
                    <img src="../assets/profile-pic.jpg" alt="Foto Profil" />
                </div>
            </header>

            <section class="stats-cards">
                <main>
                    <div class="form-aspirasi-card">
        <div class="form-header">
                <h2>Buat Aspirasi Baru</h2>
            <p>Sampaikan ide atau keluhan Anda di sini.</p>
        </div>
        <form action="#" method="POST">
            <div class="input-group">
                <label for="title">Judul Aspirasi</label>
                <input type="text" id="title" name="title" class="input-field" required>
            </div>
            <div class="input-group">
                <label for="content">Isi Aspirasi</label>
                <textarea id="content" name="content" class="input-field" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Aspirasi</button>
            <div style="display:flex; justify-content:center; margin-top:15px;">
                <a href="dashboard.php" style="text-decoration:none; width:100px; color:#4a90e2; padding:6px 12px; border-radius:6px; text-align:center;">Kembali</a>
            </div>
        </form>
    </div>
                </main>

    </div>

    
</body>
</html>
