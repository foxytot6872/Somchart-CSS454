<?php
require_once('Connect.php');

// ✅ Safe session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Check access
if (empty($_SESSION['user_key']) || empty($_SESSION['user_table']) || empty($_GET['fid'])) {
    header("Location: login.php");
    exit;
}

$AESkey = $_SESSION["user_key"];
$tablename = $_SESSION["user_table"];
$fileId = intval($_GET['fid']);

// ✅ Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = intval($_POST['delete_id']);

    $stmt1 = $mysqli->prepare("DELETE FROM `$tablename` WHERE FILE_ID = ?");
    $stmt1->bind_param("i", $delId);
    $stmt1->execute();

    $stmt2 = $mysqli->prepare("DELETE FROM `all_file` WHERE FILE_ID = ?");
    $stmt2->bind_param("i", $delId);
    $stmt2->execute();

    header("Location: filelist.php");
    exit;
}

// ✅ Fetch file
$stmt = $mysqli->prepare("SELECT * FROM `$tablename` WHERE FILE_ID = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("❌ File not found.");
}

$row = $result->fetch_assoc();
$Filename = $row['FILE_NAME'];
$ciphertext_b64 = $row['CIPHERTEXT'];
$FileTime = $row['UPLOADTIMESTAMP'];

// ✅ Decrypt
$plaintext = openssl_decrypt(base64_decode($ciphertext_b64), "AES-128-ECB", $AESkey, OPENSSL_RAW_DATA);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View File</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, #ffecd2, #fcb69f);
      padding: 50px;
      display: flex;
      justify-content: center;
    }

    .box {
      background-color: white;
      padding: 30px;
      border-radius: 15px;
      width: 600px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #333;
    }

    .content-block {
      margin-top: 20px;
      display: flex;
      flex-direction: column;
    }

    .content-block label {
      font-weight: bold;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .content-block textarea {
      width: 95%;
      min-height: 150px;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      resize: vertical;
      background-color: #f9f9f9;
    }

    .btn {
      display: inline-block;
      margin-top: 20px;
      margin-right: 10px;
      padding: 10px 18px;
      font-size: 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      color: white;
    }

    .back-btn {
      background-color: #6c757d;
    }

    .delete-btn {
      background-color: #dc3545;
    }

    .delete-btn:hover {
      background-color: #c82333;
    }

    .back-btn:hover {
      background-color: #5a6268;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2>📄 View File</h2>

    <p><strong>Filename:</strong> <?= htmlspecialchars($Filename) ?></p>

    <div class="content-block">
      <label for="content">Content:</label>
      <textarea id="content" readonly><?= htmlspecialchars($plaintext) ?></textarea>
    </div>

    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this file?');">
      <input type="hidden" name="delete_id" value="<?= $fileId ?>">
      <button type="submit" class="btn delete-btn">🗑 Delete File</button>
    </form>

    <a href="filelist.php" class="btn back-btn">⬅ Back to File List</a>
  </div>
</body>
</html>


<!-- Hello -->