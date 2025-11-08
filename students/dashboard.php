<?php
session_start();
include('../config/connection.php');


//memastikan bahwa student yang login
if (!isset($_SESSION['id_student']) || $_SESSION['role'] != 'student') {
    header("Location: ../logreg/login.php");
    exit;
}

$id_student = $_SESSION['id_student'];

// ambil data profil siswa yang sedang login
$query = "SELECT * FROM students WHERE id_student = '$id_student'";
$result = mysqli_query($conn, $query);
$student = mysqli_fetch_assoc($result);

// ambil aspirasi milik siswa yang login
$aspirasiQuery = mysqli_query($conn, 
    "SELECT * FROM aspirations WHERE id_student = '$id_student' ORDER BY date_submitted DESC"
);

// Jika tombol hapus ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_POST['delete_id']);
    
    // Pastikan aspirasi milik siswa yang sedang login
    $check = mysqli_query($conn, "SELECT * FROM aspirations WHERE id_aspiration = '$delete_id' AND id_student = '$id_student'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM aspirations WHERE id_aspiration = '$delete_id'");
        echo "<script>alert('Aspirasi berhasil dihapus!'); window.location.href='dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Aspirasi tidak ditemukan atau bukan milik Anda!');</script>";
    }
}

// menghitung statistik aspirasi siswa login
$totalAspirasi = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) AS total FROM aspirations WHERE id_student = '$id_student'"
))['total'];

$menunggu = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) AS total FROM aspirations WHERE id_student = '$id_student' AND status = 'Pending'"
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
            <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="buat-aspirasi.php"><i class="fas fa-plus-circle"></i> Buat Aspirasi</a></li>
            <li><a href="../profile/edit-profile.php"><i class="fas fa-user"></i> Profil Saya</a></li>
            <li><a href="../logreg/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <h3>Dashboard Siswa</h3>
            <?php
// Pastikan variabel $student atau $studentData sudah ada
$foto_path = '../' . ($student['foto_profile'] ?? 'assets/profile-pic.jpg');

// Cek apakah file gambar benar-benar ada di server
if (!file_exists($foto_path) || empty($student['foto_profile'])) {
    $foto_path = '../assets/profile-pic.jpg'; // fallback ke default
}
?>
            <div class="profile">
                <img 
                    src="<?= htmlspecialchars($foto_path) ?>" 
                    alt="Foto Profil"
                    width="50"
                    height="50"
                    style="border-radius:50%; object-fit:cover;">
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
                            <th>Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($aspirasiQuery)): ?>
                                <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($row['date_submitted']))) ?></td>
                                <td>
                                    <span class="status <?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="">
                                   <input type="hidden" name="delete_id" value="<?= $row['id_aspiration'] ?>">
                                        <button type="submit" style="background:#e74c3c;color:white;border:none; padding:5px 12px;border-radius:20px;cursor:pointer;">
                                            Hapus
                                        </button>
                                    </form> 
                                </td>
                                </tr>
                                <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>
