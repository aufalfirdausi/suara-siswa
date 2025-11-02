<?php
session_start();
include('../config/connection.php');

// pastikan hanya admin yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
  header("Location: ../logreg/login.php");
  exit;
}

// Ambil semua aspirasi + data siswa yang mengirim
$query = mysqli_query($conn, "
  SELECT a.id_aspiration, a.title, a.content, a.status, a.response, a.date_submitted, 
         s.name AS student_name
  FROM aspirations a
  JOIN students s ON a.id_student = s.id_student
  ORDER BY a.date_submitted DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
</head>
<body>
    <div>
        <h2>Welcome, Admin!</h2>
        <a href="../logreg/logout.php">Logout</a>
        <img src="../assets/circle-user-solid-full.svg" style="" alt="">
    </div>  
    <h3>Daftar Aspirasi Siswa</h3>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>No</th>
      <th>Nama Siswa</th>
      <th>Judul</th>
      <th>Isi Aspirasi</th>
      <th>Tanggal</th>
      <th>Status</th>
    </tr>

    <?php
    if (mysqli_num_rows($query) > 0) {
      $no = 1;
        while ($row = mysqli_fetch_assoc($query)) { ?>
  <tr>
    <td><?= $no++; ?></td>
    <td><?= $row['student_name']; ?></td>
    <td><?= $row['title']; ?></td>
    <td><?= $row['content']; ?></td>
    <td><?= $row['date_submitted']; ?></td>
    <td>
      <form action="update-status.php" method="POST">
        <input type="hidden" name="id" value="<?= $row['id_aspiration']; ?>">
        <select name="status">
          <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending <span><img src="../assets/bell-solid-full.svg" alt=""></span></option>
          <option value="In Process" <?= $row['status'] == 'In Process' ? 'selected' : ''; ?>>In Process</option>
          <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
        </select>
        <button type="submit">Update</button>
      </form>
    </td>
  </tr>
<?php
     }
        $no++;
      }
     else {
      echo "<tr><td colspan='8'>Belum ada aspirasi yang dikirim.</td></tr>";
    }
    ?>
  </table>
</body>
</html>