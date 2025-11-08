<?php
session_start();
include('../config/connection.php');

// 🔒 Pastikan hanya admin yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
  header("Location: ../logreg/login.php");
  exit;
}

$id_admin = $_SESSION['user_id'];

// 🔹 Ambil data profil admin
$query_admin = mysqli_query($conn, "SELECT * FROM admins WHERE id_admin = '$id_admin'");
$admin = mysqli_fetch_assoc($query_admin);

// 🔹 Ambil semua aspirasi dari siswa
$query_aspirasi = mysqli_query($conn, "
  SELECT a.id_aspiration, a.title, a.content, a.status, a.date_submitted,
         s.name AS student_name
  FROM aspirations a
  JOIN students s ON a.id_student = s.id_student
  ORDER BY a.date_submitted DESC
");


// tanda selesai aspirasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_done'])) {
    $update_id = mysqli_real_escape_string($conn, $_POST['update_id']);
    mysqli_query($conn, "UPDATE aspirations SET status = 'Selesai' WHERE id_aspiration = '$update_id'");
    echo "<script>alert('Status aspirasi berhasil diperbarui!'); window.location.href='dashboard.php';</script>";
    exit;
}

// Jika tombol hapus ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_POST['delete_id']);

    mysqli_query($conn, "DELETE FROM aspirations WHERE id_aspiration = '$delete_id'");
    echo "<script>alert('Aspirasi berhasil dihapus!'); window.location.href='dashboard.php';</script>";
    exit;
}


// 🔹 Hitung statistik sederhana
$total_aspirasi = mysqli_num_rows($query_aspirasi);
$menunggu = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM aspirations WHERE status='Pending'"));
$selesai = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM aspirations WHERE status='Selesai'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin</title>
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
        <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
        <li><a href="../profile/edit-profile.php"><i class="fas fa-user"></i> Profil Saya</a></li>
        <li><a href="../logreg/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <header class="main-header">
        <h3>Dashboard Admin</h3>
        <div class="profile">
          <span><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></span>
          <?php
            $foto_path = '../' . ($admin['foto_profile'] ?? 'assets/profile-pic.jpg');
            if (!file_exists($foto_path) || empty($admin['foto_profile'])) {
              $foto_path = '../assets/profile-pic.jpg';
            }
          ?>
          <img src="<?= htmlspecialchars($foto_path) ?>" alt="Foto Profil" width="50" height="50" style="border-radius:50%;">
        </div>
      </header>

      <section class="stats-cards">
        <div class="card">
          <div class="card-header">
            <div class="card-icon blue"><i class="fas fa-paper-plane"></i></div>
            <div class="card-info">
              <h4>Total Aspirasi</h4>
              <p><?= $total_aspirasi ?></p>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="card-icon yellow"><i class="fas fa-clock"></i></div>
            <div class="card-info">
              <h4>Menunggu Respon</h4>
              <p><?= $menunggu ?></p>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="card-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="card-info">
              <h4>Selesai Diproses</h4>
              <p><?= $selesai ?></p>
            </div>
          </div>
        </div>
      </section>

      <div class="table-container">
        <h4>Aspirasi Terbaru</h4>
        <table>
          <thead>
            <tr>
              <th>Nama Siswa</th>
              <th>Judul Aspirasi</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Action</th>
              <th>Hapus</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($total_aspirasi > 0): ?>
              <?php mysqli_data_seek($query_aspirasi, 0); // reset pointer ?>
              <?php while ($row = mysqli_fetch_assoc($query_aspirasi)): ?>
                <tr>
                  <td><?= htmlspecialchars($row['student_name']) ?></td>
                  <td><?= htmlspecialchars($row['title']) ?></td>
                  <td><?= htmlspecialchars(date('d M Y', strtotime($row['date_submitted']))) ?></td>
                  <td>
                    <span class="status <?= strtolower($row['status']) ?>">
                      <?= htmlspecialchars($row['status']) ?>
                    </span>

                    <!-- Tombol untuk tandai selesai -->
                        <?php if ($row['status'] != 'Selesai'): ?>
                          <form method="POST" action="" style="display:inline; padding-left:30px;">
                            <input type="hidden" name="update_id" value="<?= $row['id_aspiration'] ?>">
                            <button type="submit" name="mark_done" 
                              style="background:none;border:none;color:#27ae60;cursor:pointer;font-size:18px;"
                              title="Tandai Selesai">
                              <i class="fas fa-check-circle"></i>
                            </button>
                          </form>
                        <?php endif; ?>
                        <!-- Akhir tombol tandai selesai & status -->
                  </td>
                  <td><a href="#" class="btn-detail" data-id="<?= $row['id_aspiration'] ?>">Lihat Detail</a></td> 
                  <td>
                                    <form method="POST" action="">
                                   <input type="hidden" name="delete_id" value="<?= $row['id_aspiration'] ?>">
                                        <button type="submit" style="background:#e74c3c;color:white;border:none; padding:5px 12px;border-radius:20px;cursor:pointer;">
                                            Hapus
                                        </button>
                                    </form> 
                                </td>
                </tr>
                <tr class="desc-aspiration" id="desc-<?= $row['id_aspiration'] ?>" style="display:none;">
                  <td colspan="5"><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                </tr> 
              <?php endwhile; ?>
              <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  //script untuk toggle detail aspirasi
  <script>
document.querySelectorAll('.btn-detail').forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault(); // jangan pindah halaman
    const id = this.getAttribute('data-id');
    const row = document.getElementById('desc-' + id);

    // toggle tampil/sembunyi
    if (row.style.display === 'none' || row.style.display === '') {
      row.style.display = 'table-row';
      this.textContent = 'Sembunyikan';
    } else {
      row.style.display = 'none';
      this.textContent = 'Lihat Detail';
    }
  });
});
</script>

</body>
</html>
