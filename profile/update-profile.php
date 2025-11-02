<?php
session_start();
include('../config/connection.php');

// Pastikan user sudah login
if (!isset($_SESSION['role'])) {
    header("Location: ../logreg/login.php");
    exit();
}

// Tentukan role & ID user
if ($_SESSION['role'] === 'admin') {
    $table = 'admins';
    $id_field = 'id_admin';
    $id_user = $_SESSION['id_admin'];
    $upload_folder = '../uploads/admin/';
} else {
    $table = 'students';
    $id_field = 'id_student';
    $id_user = $_SESSION['id_student'];
    $upload_folder = '../uploads/students/';
}

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

    // Jika upload foto baru
    if (!empty($_FILES['foto_profile']['name'])) {
        $foto_name = $_FILES['foto_profile']['name'];
        $foto_tmp = $_FILES['foto_profile']['tmp_name'];

        // Bersihkan nama file
        $foto_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $foto_name);
        $foto_name = str_replace(' ', '_', $foto_name);

        $file_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            // Buat nama unik biar tidak bentrok
            $unique_name = uniqid() . '_' . $foto_name;
            $target_file = $upload_folder . $unique_name;

            if (move_uploaded_file($foto_tmp, $target_file)) {
                // Hapus foto lama kalau ada
                if (!empty($user['foto_profile']) && file_exists('../' . $user['foto_profile'])) {
                    unlink('../' . $user['foto_profile']);
                }

                // Simpan path baru ke DB
                $path_db = "uploads/" . $_SESSION['role'] . "/" . $unique_name;

                $update = "UPDATE $table SET name='$name', foto_profile='$path_db' WHERE $id_field='$id_user'";
                mysqli_query($conn, $update);

                echo "<p style='color:green;'>✅ Profil berhasil diperbarui.</p>";

                // Refresh data
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

        echo "<p style='color:green;'>✅ Nama berhasil diperbarui.</p>";
        $user['name'] = $name;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Profil</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Edit Profil (<?= ucfirst($_SESSION['role']) ?>)</h2>

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Nama:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>

        <label>Foto Profil:</label><br>
        <input type="file" name="foto_profile" accept="image/*"><br><br>

        <img src="../<?= htmlspecialchars($user['foto_profile'] ?? 'assets/profile-pic.jpg') ?>" 
             alt="Foto Profil" 
             width="120" 
             height="120" 
             style="border-radius:50%; border:1px solid #ccc;"><br><br>

        <button type="submit" name="submit">Simpan Perubahan</button>
    </form>
</body>
</html>
