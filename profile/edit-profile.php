<?php
session_start();
include('../config/connection.php');

// Pastikan user sudah login
if (!isset($_SESSION['role'])) {
    header("Location: ../logreg/login.php");
    exit();
}

// Tentukan role & ID berdasarkan session
// Tentukan role & ID berdasarkan session
if ($_SESSION['role'] === 'admin') {
    $table = 'admins';
    $id_field = 'id_admin';
    $id_user = $_SESSION['id_admin'] ?? $_SESSION['user_id'] ?? null;
} else {
    $table = 'students';
    $id_field = 'id_student';
    $id_user = $_SESSION['id_student'] ?? $_SESSION['user_id'] ?? null;
}

// Tentukan folder upload dan path DB berdasarkan role
$role_folder = ($_SESSION['role'] === 'admin') ? 'admin' : 'student';
$upload_folder = "../uploads/$role_folder/";


// Ambil data user dari database
$query = "SELECT * FROM $table WHERE $id_field = '$id_user'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("❌ Data pengguna tidak ditemukan.");
}

// Jika form disubmit
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);

    // Pastikan folder upload ada
    if (!is_dir($upload_folder)) {
        mkdir($upload_folder, 0777, true);
    }

    // Cek apakah upload foto baru
    if (!empty($_FILES['foto_profile']['name'])) {
        $foto_name = $_FILES['foto_profile']['name'];

        // Bersihkan nama file: hapus karakter aneh & ubah spasi ke underscore
        $foto_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $foto_name);
        $foto_name = str_replace(' ', '_', $foto_name);

        $foto_tmp = $_FILES['foto_profile']['tmp_name'];
        $file_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            // Nama unik agar tidak bentrok
            $unique_name = uniqid() . '_' . $foto_name;
            $target_file = $upload_folder . $unique_name;

            if (move_uploaded_file($foto_tmp, $target_file)) {
                // hapus foto lama jika ada
                if (!empty($user['foto_profile']) && file_exists('../' . $user['foto_profile'])) {
                    unlink('../' . $user['foto_profile']);
                }

                // simpan path ke db (relatif dari root)
                $path_db = "uploads/$role_folder/$unique_name";

                $update = "update $table set name='$name', foto_profile='$path_db' where $id_field='$id_user'";
                mysqli_query($conn, $update);

                echo "<p style='color:green;'>✅ profil berhasil diperbarui.</p>";

                // refresh data di memori
                $user['name'] = $name;
                $user['foto_profile'] = $path_db;
            } else {
                echo "<p style='color:red;'>❌ Gagal mengunggah file.</p>";
            }
        } else {
            echo "<p style='color:red;'>❌ Jenis file tidak diizinkan. Gunakan JPG, PNG, atau GIF.</p>";
        }
    } else {
        // Jika tidak upload foto baru
        $update = "UPDATE $table SET name='$name' WHERE $id_field='$id_user'";
        mysqli_query($conn, $update);

        // echo "<p style='color:green;'>✅ Nama berhasil diperbarui.</p>";
        // $user['name'] = $name;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Menghilangkan tampilan default input file */
        
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-bullhorn"></i>
            <h2>Suara Siswa</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="<?= ($_SESSION['role'] === 'admin') ?'../admin/dashboard.php' : '../students/dashboard.php'; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="active"><a href="#"><i class="fas fa-user"></i> Profil Saya</a></li>
            <li><a href="../logreg/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <div class="main-content-pro">
        <h2 style="margin-top:30px;">Edit Profil <?= ucfirst($_SESSION['role']) ?></h2>
        <div class="content-inner">
            <form action="" method="POST" enctype="multipart/form-data">        
                <img src="../<?= htmlspecialchars($user['foto_profile'] ?? 'assets/profile-pic.jpg') ?>" 
                     alt="Foto Profil" 
                     width="120" 
                     height="120" 
                     style="border-radius:50%; border:1px solid #ccc;"><br><br>

                <label class="label-profile">Nama: 
                    <input type="text" name="name" style="border:none; padding-left:10px; font-size:16px; width:30em;" value="<?= htmlspecialchars($user['name']) ?>" required>
                </label><br><br>

                <label class="custom-file-upload">
                    Pilih Foto Profil
                    <input type="file" name="foto_profile" accept="image/*">
                </label><br>
                <button class="custom-file-upload save" type="submit" name="submit">Save</button>

            </form>
        </div>
    </div>
</div>
</body>
</html>
