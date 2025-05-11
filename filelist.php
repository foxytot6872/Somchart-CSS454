<?php
// File: file_list.php

$host = 'localhost';
$db   = 'your_database';
$user = 'your_db_user';
$pass = 'your_db_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id, filename, filetype, upload_time FROM uploads ORDER BY upload_time DESC");
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$uploadedCount = isset($_GET['uploaded']) ? intval($_GET['uploaded']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Uploaded Files</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: #f7f7f7;
      padding: 40px;
    }

    h2 {
      text-align: center;
      color: #333;
    }

    table {
      width: 90%;
      margin: 20px auto;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    th, td {
      padding: 12px 20px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #4CAF50;
      color: white;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .upload-again {
      display: block;
      width: 200px;
      margin: 30px auto;
      padding: 10px 20px;
      text-align: center;
      background-color: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 8px;
    }

    .success-message {
      text-align: center;
      color: green;
      font-size: 16px;
    }
  </style>
</head>
<body>

  <h2>Uploaded Files</h2>

  <?php if ($uploadedCount > 0): ?>
    <div class="success-message"><?= $uploadedCount ?> file(s) uploaded successfully!</div>
  <?php endif; ?>

  <table>
    <tr>
      <th>Filename</th>
      <th>File Type</th>
      <th>Upload Time</th>
    </tr>
    <?php foreach ($files as $file): ?>
      <tr>
        <td><?= htmlspecialchars($file['filename']) ?></td>
        <td><?= htmlspecialchars($file['filetype']) ?></td>
        <td><?= $file['upload_time'] ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <a class="upload-again" href="upload.php">Upload More Files</a>
</body>
</html>
