<?php
include ('../config/connection.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  $role = 'student'; //default role

  // Hash password biar aman
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

   if ($role == 'student') {
    // Cek apakah email sudah ada di tabel students
    $check = mysqli_query($conn, "SELECT * FROM students WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
      echo "Email sudah terdaftar sebagai student!";
    } else {
      // Simpan ke tabel students
      $query = "INSERT INTO students (name, email, password)
                VALUES ('$name', '$email', '$hashed_password')";
      if (mysqli_query($conn, $query)) {
        echo "<script>
        alert('Registrasi berhasil! Silakan login.');
        window.location.href='login.php';
      </script>";
      } else {
        echo "Error: " . mysqli_error($conn);
      }
    }
  } else {
    echo "Pilih role terlebih dahulu!";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - Suara Siswa</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
  </head>
  <body>
    <div class="auth-container">
      <div class="auth-card card-regis">
        <div class="auth-panel form-panel">
          <div class="form-header">
            <h2>Buat Akun Baru</h2>
            <p>Bergabunglah dengan Suara Siswa sekarang!</p>
          </div>
          <form action="register.php" method="POST">
            <div class="input-group">
              <label for="name">Nama Lengkap</label>
              <input type="text" id="name" name="name" class="input-field" required />
            </div>
            <div class="input-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" class="input-field" required />
            </div>
            <div class="input-group">
              <label for="password">Password</label>
              <input
                type="password"
                id="password"
                name="password"
                class="input-field"
                required
              />
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
          </form>
          <p class="auth-switch">
            Sudah punya akun? <a href="login.php">Login di sini</a>
          </p>
        </div>
      </div>
    </div>
  </body>
</html>
