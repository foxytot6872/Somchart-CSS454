<?php
require_once('Connect.php');
session_start();

// 🔐 Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_table'])) {
    header("Location: login.php");
    exit;
}

$userid = $_SESSION['user_id'];
$tablename = $_SESSION['user_table'];
$AESkey = $_SESSION["user_key"];


//Coming from edit_file.php
if (isset($_POST['Edit_Submit'])) {
  $editFileID = $_POST["fileid"];
  $editFileName = $mysqli->real_escape_string($_POST["editFileName"]);
  $editFileContent = $_POST["editFileContent"];
  $newFiletimestamp = date('Y-m-d H:i:s');

  $newciphertext = openssl_encrypt($editFileContent, "AES-128-ECB", $AESkey, OPENSSL_RAW_DATA);
  $newciphertext_b64 = base64_encode($newciphertext);
  $newFilehash = hash('sha256', $newciphertext_b64);
  $newFileHMAChash = hash_hmac('sha256', $newciphertext_b64, $newFiletimestamp);

  $q1 = "UPDATE all_file SET FILE_NAME = '$editFileName', CIPHERTEXT = '$newciphertext_b64', HMACDIGEST = '$newFileHMAChash' WHERE FILE_ID = '$editFileID'";
    $mysqli->query($q1) or die($mysqli->error);

  $q2 = "UPDATE $tablename SET FILE_NAME = '$editFileName', MERKLE_HASH = '$newFilehash', CIPHERTEXT = '$newciphertext_b64', HMACDIGEST = '$newFileHMAChash', UPLOADTIMESTAMP = '$newFiletimestamp' WHERE FILE_ID = '$editFileID'";
    $mysqli->query($q2) or die($mysqli->error);
}

// 🗑 Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $fileId = intval($_POST['delete_id']);

    $stmt1 = $mysqli->prepare("DELETE FROM `$tablename` WHERE FILE_ID = ?");
    $stmt1->bind_param("i", $fileId);
    $stmt1->execute();

    $stmt2 = $mysqli->prepare("DELETE FROM `all_file` WHERE FILE_ID = ?");
    $stmt2->bind_param("i", $fileId);
    $stmt2->execute();
}

// 📄 Fetch all user files
$query = "SELECT FILE_ID, FILE_NAME, UPLOADTIMESTAMP FROM `$tablename` ORDER BY TREE_INDEX ASC";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Files</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, #d9afd9, #97d9e1);
      padding: 40px;
    }

    h2 {
      text-align: center;
      color: #333;
    }

    .action-buttons {
      text-align: center;
      margin-bottom: 20px;
    }

    .top-btn {
      display: inline-block;
      background-color: #007BFF;
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      text-decoration: none;
      margin: 0 10px;
      font-size: 14px;
    }

    .top-btn:hover {
      background-color: #0056b3;
    }

    table {
      width: 90%;
      margin: 0 auto;
      border-collapse: collapse;
      background-color: white;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px 18px;
      border-bottom: 1px solid #ccc;
      text-align: left;
      font-size: 14px;
    }

    th {
      background-color: #007BFF;
      color: white;
    }

    tr:hover {
      background-color: #f9f9f9;
    }

    .btn {
      display: inline-block;
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      cursor: pointer;
      text-decoration: none;
    }

    .view-btn {
      background-color: #17a2b8;
      color: white;
    }

    .delete-btn {
      background-color: #dc3545;
      color: white;
    }

    .delete-btn:hover {
      background-color: #c82333;
    }
  </style>
</head>
<body>

  <h2>📁 Your Uploaded Files</h2>

  <!-- 🔘 Integrity + Upload + Retrieve Buttons -->
<div class="action-buttons">
    <a href="integrity_report.php" class="top-btn">🔍 Integrity Report</a>
    <a href="upload.php" class="top-btn" style="background-color: #28a745;">⬆ Upload New File</a>
    <a href="retrieve_file.php" class="top-btn" style="background-color: #ffc107; color: black;">📥 Search</a>
</div>


  <table>
    <tr>
      <th>#</th>
      <th>File Name</th>
      <th>Uploaded At</th>
      <th>Actions</th>
    </tr>

    <?php
    if ($result && $result->num_rows > 0) {
      $i = 1;
      while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$i}</td>";
        echo "<td>" . htmlspecialchars($row['FILE_NAME']) . "</td>";
        echo "<td>" . $row['UPLOADTIMESTAMP'] . "</td>";
        echo "<td>";
        echo "<a class='btn view-btn' href='view_file.php?fid={$row['FILE_ID']}'>View</a> ";
        echo "<form style='display:inline;' method='POST' action='filelist.php' onsubmit=\"return confirm('Are you sure you want to delete this file?');\">";
        echo "<input type='hidden' name='delete_id' value='{$row['FILE_ID']}'>";
        echo "<button type='submit' class='btn delete-btn'>Delete</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
        $i++;
      }
    } else {
      echo "<tr><td colspan='4' style='text-align:center;'>No files found.</td></tr>";
    }
    ?>
  </table>

</body>
</html>

<!-- Helo -->