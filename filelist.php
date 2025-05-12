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

// 🗑 Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $fileId = intval($_POST['delete_id']);

    // Delete from user's private table
    $stmt1 = $mysqli->prepare("DELETE FROM `$tablename` WHERE FILE_ID = ?");
    $stmt1->bind_param("i", $fileId);
    $stmt1->execute();

    // Optional: also delete from all_file table
    $stmt2 = $mysqli->prepare("DELETE FROM `all_file` WHERE FILE_ID = ?");
    $stmt2->bind_param("i", $fileId);
    $stmt2->execute();
}

// 📄 Fetch all user files
$query = "SELECT FILE_ID, FILE_NAME, UPLOADTIMESTAMP FROM `$tablename` ORDER BY UPLOADTIMESTAMP DESC";
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

    .back-btn {
      display: block;
      margin: 30px auto;
      background-color: #28a745;
      color: white;
      padding: 12px 20px;
      text-align: center;
      width: 200px;
      text-decoration: none;
      border-radius: 8px;
    }

    .back-btn:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>

  <h2>📁 Your Uploaded Files</h2>
    <a href="integrity_report.php?>">Integrity Report</a>
    
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

        // 🗑 Delete form
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

  <a class="back-btn" href="upload.php">⬅ Back to Upload</a>

</body>
</html>