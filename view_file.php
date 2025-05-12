<?php
require_once('Connect.php');
session_start();

if (empty($_SESSION['user_key']) || empty($_GET['fid'])) {
  header("Location: login.php");
  exit;
}

$AESkey = $_SESSION["user_key"];
$tablename = $_SESSION['user_table'];
$fileId = intval($_GET['fid']); 

// Securely fetch the file from the user's table
$stmt = $mysqli->prepare("SELECT * FROM $tablename WHERE FILE_ID = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    $Filename = $row['FILE_NAME'];
    $ciphertext_b64 = $row['CIPHERTEXT'];
    $FileHMAChash = $row['HMACDIGEST'];
    $Filetimestamp = $row['UPLOADTIMESTAMP'];

    // HMAC verification
    $NewHMAC = hash_hmac('sha256', $ciphertext_b64, $Filetimestamp, false);

    if (hash_equals($FileHMAChash, $NewHMAC)) {
        $Filecontent = openssl_decrypt(base64_decode($ciphertext_b64), "AES-128-ECB", $AESkey, OPENSSL_RAW_DATA);
    } else {
        echo "<script>alert('⚠️ File integrity check failed.'); window.location.href='filelist.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('❌ File not found.'); window.location.href='filelist.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View File</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, #fddb92, #d1fdff);
      height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .upload-box {
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      width: 480px;
      text-align: left;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    label {
      font-weight: bold;
      margin-top: 12px;
      display: block;
      color: #555;
    }

    textarea {
      width: 100%;
      padding: 12px;
      margin-top: 8px;
      border: 1px solid #ccc;
      border-radius: 8px;
      resize: vertical;
      background-color: #f9f9f9;
      font-size: 14px;
    }

    .gotofilelistbox {
      display: block;
      background-color: #007BFF;
      color: white;
      padding: 12px;
      margin-top: 20px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      text-align: center;
      text-decoration: none;
    }

    .gotofilelistbox:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="upload-box">
    <h2>📄 File Viewer</h2>

    <label>File Name</label>
    <div><?= htmlspecialchars($Filename) ?></div>

    <label>Decrypted Content</label>
    <textarea rows="6" readonly><?= htmlspecialchars($Filecontent) ?></textarea>

    <a class="gotofilelistbox" href="filelist.php">⬅ Back to File List</a>
  </div>
</body>
</html>
