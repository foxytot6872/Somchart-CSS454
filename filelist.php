<?php require_once('Connect.php');
session_start();

if (empty($_SESSION['user_table'])) {
  header("Location: login.php");
  exit;
}
$tablename = $_SESSION['user_table'];


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

  <table>
  <thead>
    <tr>
      <th>No</th>
      <th>Filename</th>
      <th>Upload Time</th>
    </tr>
  </thead>
  <tbody>
    <?php $q="SELECT TREE_INDEX, FILE_ID, FILE_NAME, UPLOADTIMESTAMP FROM $tablename WHERE CIPHERTEXT IS NOT NULL";
					$result=$mysqli->query($q);
					if(!$result){
						// what happens here
						echo "Insert failed. Error: ".$mysqli->error;
					}
          while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['TREE_INDEX']) ?></td>
        <td>
          <a href="view_file.php?fid=<?= $row['FILE_ID'] ?>">
            <?= htmlspecialchars($row['FILE_NAME']) ?>
          </a>
        </td>
        <td><?= htmlspecialchars($row['UPLOADTIMESTAMP']) ?></td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

  <a class="upload-again" href="upload.php">Upload More Files</a>
</body>
</html>
