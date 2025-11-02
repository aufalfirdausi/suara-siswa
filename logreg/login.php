
<?php
session_start();
include('../config/connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $role = mysqli_real_escape_string($conn, $_POST['role']);
  $otorization = isset($_POST['otorization']) ? mysqli_real_escape_string($conn, $_POST['otorization']) : '';

  if ($role == 'student') {
    // Login student
    $query = mysqli_query($conn, "SELECT * FROM students WHERE email='$email'");
    if (mysqli_num_rows($query) > 0) {
      $student = mysqli_fetch_assoc($query);
      if (password_verify($password, $student['password'])) {
        $_SESSION['id_student'] = $student['id_student'];
        $_SESSION['role'] = 'student';
        header("Location: ../students/dashboard.php");
        exit;
      } else {
        echo "<script>alert('Password salah!');</script>";
      }
    } else {
      echo "<script>alert('Email tidak ditemukan sebagai student!');</script>";
    }

  } elseif ($role == 'admin') {
    // Login admin
    $query = mysqli_query($conn, "SELECT * FROM admins WHERE email='$email'");
    if (mysqli_num_rows($query) > 0) {
      $admin = mysqli_fetch_assoc($query);

      // Verifikasi password dulu
      if (password_verify($password, $admin['password'])) {

        // Lalu cek kode otorisasi
        if ($otorization === "SEKOLAHMAJU") {
          $_SESSION['user_id'] = $admin['id_admin'];
          $_SESSION['role'] = 'admin';
          header("Location: ../admin/dashboard.php");
          exit;
        } else {
          echo "<script>alert('Kode otorisasi salah! Hubungi administrator utama.');</script>";
        }

      } else {
        echo "<script>alert('Password salah!');</script>";
      }
    } else {
      echo "<script>alert('Email tidak ditemukan sebagai admin!');</script>";
    }

  } else {
    echo "<script>alert('Pilih role terlebih dahulu!');</script>";
  }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Suara Siswa</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-panel form-panel">
                <div class="form-header">
                    <h2>Selamat Datang Kembali!</h2>
                    <p>Login untuk melanjutkan ke dashboard Anda.</p>
                </div>
                <form action="" method="POST">
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="input-field" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="input-field" required>
                    </div>

                    <div>
                        <select id="role" name="role" class="input-field" required>
                            <option value="" disabled selected>Pilih role</option>
                            <option value="student">Siswa</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="otorization">Kode Otorisasi (Khusus Admin)</label>
                        <input type="password" id="otorization" name="otorization" class="input-field" placeholder="Opsional">
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <p class="auth-switch">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            </div>
            <div class="auth-panel side-panel">
                <h2>Suara Siswa</h2>
                <p>Platform aspirasi untuk sekolah yang lebih baik. Sampaikan idemu, wujudkan perubahan.</p>
            </div>
        </div>
    </div>
</body>
</html>