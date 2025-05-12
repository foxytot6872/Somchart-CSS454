<?php require_once('Connect.php');
session_start();

if (empty($_SESSION['user_table'])) {
  header("Location: login.php");
  exit;
}
$id = $_SESSION["user_id"];
$AESkey = $_SESSION["user_key"];
$tablename = $_SESSION['user_table'];


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
      <th>Download File</th>
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
        <td>
          <a href="download_file.php?fid=<?= $row['FILE_ID'] ?>">⬇ Retrieve</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

  <a class="upload-again" href="upload.php">Upload More Files</a>
</body>
</html>
