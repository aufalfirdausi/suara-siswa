<?php
session_start();
include ('../config/connection.php');

// memastikan bahwa student yang login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../logreg/login.php");
    exit;
}

$id_student = $_SESSION['user_id'];

// ngambil data aspirasi punya siswa yg login
$query = mysqli_query($conn, "SELECT * FROM aspirations WHERE id_student = '$id_student' ORDER BY date_submitted DESC");

// menghitung total aspirasi milik siswa login
$totalAspirasi = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) AS total FROM aspirations WHERE id_student = '$id_student'"
))['total'];

$menunggu = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) AS total FROM aspirations WHERE id_student = '$id_student' AND status = 'Menunggu'"
))['total'];

$selesai = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) AS total FROM aspirations WHERE id_student = '$id_student' AND status = 'Selesai'"
))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Siswa - Suara Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-bullhorn"></i>
                <h2>Suara Siswa</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><a href="dashboard.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="buat-aspirasi.php"><i class="fas fa-plus-circle"></i>Buat Aspirasi</a></li>
                <li><a href="../profile/edit-profile.php"><i class="fas fa-user"></i> Profil Saya</a></li>
                <li><a href="../logreg/login.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <h3>Dashboard Siswa</h3>
                <div class="profile">
                    <img src="../uploads/<?= htmlspecialchars($studentData['foto_profile']); ?>" alt="Profile"> 
                </div>
            </header>

            <section class="stats-cards">
    <div class="card">
        <div class="card-icon blue">
            <i class="fas fa-paper-plane"></i>
        </div>
        <div class="card-info">
            <h4>Total Aspirasi Saya</h4>
            <p><?= $totalAspirasi ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-icon yellow">
            <i class="fas fa-clock"></i>
        </div>
        <div class="card-info">
            <h4>Menunggu Respon</h4>
            <p><?= $menunggu ?></p>
        </div>
    </div>

    <div class="card">
        <div class="card-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="card-info">
            <h4>Sudah Selesai</h4>
            <p><?= $selesai ?></p>
        </div>
    </div>
</section>

            
            <section class="recent-aspirations">
                <h4>Riwayat Aspirasi Kamu</h4>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul Aspirasi</th>
                                <th>Tanggal Kirim</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
      if (mysqli_num_rows($query) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_assoc($query)) {
        echo "<tr>
                <td>{$row['title']}</td>
                <td>{$row['date_submitted']}</td>
                <td>{$row['status']}</td>
                <td><a href='hapus_aspirasi.php?id={$row['id_aspiration']}'
                style='background-color:#ff6b6b; color:white; padding:6px 12px; border-radius:6px; text-decoration:none; font-weight:bold;'>
                Delete</a></td>
             </tr>";
             $no++;
        }
      } else {
        echo "<tr><td colspan='7'> No aspiration submitted yet.</td></tr>";
      }
      ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

</body>
</html>
