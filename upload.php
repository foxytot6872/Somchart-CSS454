<?php
require_once('Connect.php');
session_start();

// LOGIN handling (if called from login)
if (isset($_POST['Login_Submit'])) {
    $username = $_POST['username'];
    $passwd = $_POST['password'];

    if (empty($username) || empty($passwd)) {
        echo "Username and password cannot be empty.";
        exit;
    }

    $stmt = $mysqli->prepare("SELECT USER_ID, USER_PASSWORD, USER_KEY FROM users WHERE USERNAME = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($passwd, $row['USER_PASSWORD'])) {
            $_SESSION["user_id"] = $row['USER_ID'];
            $_SESSION["user_key"] = $row['USER_KEY'];
            $_SESSION["user_table"] = "userdb_" . $row['USER_ID'];
        } else {
            session_destroy();
            header("Location: login.php?error=1");
            exit;
        }
    } else {
        session_destroy();
        header("Location: login.php?error=1");
        exit;
    }
    $stmt->close();
}

// FILE UPLOAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Upload_Submit'])) {
    $id = $_SESSION["user_id"];
    $AESkey = $_SESSION["user_key"];
    $tablename = $_SESSION["user_table"];

    $Filename = $_POST['File_Name'];
    $Filecontent = $_POST['File_Content'];
    $Filetimestamp = date('Y-m-d H:i:s');

    $ciphertext = openssl_encrypt($Filecontent, "AES-128-ECB", $AESkey, OPENSSL_RAW_DATA);
    $ciphertext_b64 = base64_encode($ciphertext);
    $Filehash = hash('sha256', $ciphertext_b64);
    $FileHMAChash = hash_hmac('sha256', $ciphertext_b64, $Filetimestamp);

    $q1 = "INSERT INTO all_file (FILE_NAME, USER_ID, CIPHERTEXT, HMACDIGEST)
           VALUES ('$Filename', '$id', '$ciphertext_b64', '$FileHMAChash')";
    $mysqli->query($q1) or die($mysqli->error);
    $Fileid = $mysqli->insert_id;

    $q2 = "INSERT INTO $tablename (FILE_ID, USER_ID, FILE_NAME, MERKLE_HASH, CIPHERTEXT, HMACDIGEST, NODE_TYPE, UPLOADTIMESTAMP)
           VALUES ('$Fileid', '$id', '$Filename', '$Filehash', '$ciphertext_b64', '$FileHMAChash', 'Leaf', '$Filetimestamp')";
    if (!$mysqli->query($q2)) {
        echo "Insert failed: " . $mysqli->error;
    }

    $q3 = "UPDATE users SET FILE_NUM = FILE_NUM + 1 WHERE USER_ID = '$id'";
    $mysqli->query($q3) or die($mysqli->error);


    // Step 1: reload *all* leaves (from all_file) in chronological order
    $leafs = [];
    $stmtL = $mysqli->prepare("
        SELECT FILE_ID, FILE_NAME, MERKLE_HASH, CIPHERTEXT, HMACDIGEST, UPLOADTIMESTAMP
        FROM `{$tablename}`
        WHERE USER_ID = ? AND NODE_TYPE = 'Leaf'
        ORDER BY FILE_ID 
    ");
    $stmtL->bind_param("i", $id);
    $stmtL->execute();
    $resL = $stmtL->get_result();
    while ($r = $resL->fetch_assoc()) {
        $leafs[] = $r;
    }
    $stmtL->close();

    // Step 1.1: clear out the old Merkle table
    $mysqli->query("TRUNCATE TABLE `{$tablename}`")
        or die("Could not clear Merkle table: " . $mysqli->error);

    // Step 2: re-insert leaves into your Merkle table
    $stmtIns = $mysqli->prepare("
      INSERT INTO `{$tablename}`
        (FILE_ID, USER_ID, FILE_NAME, MERKLE_HASH, CIPHERTEXT, HMACDIGEST, NODE_TYPE, LEFTCHILD, UPLOADTIMESTAMP)
      VALUES (?,?,?,?,?,?,?,?,?)
    ");
    foreach ($leafs as $leaf) {
        $zero      = null;
        $nodeType = 'Leaf';
        $stmtIns->bind_param(
          "iisssssis",
          $leaf['FILE_ID'],       // FILE_ID
          $id,                    // USER_ID
          $leaf['FILE_NAME'],     // FILE_NAME
          $leaf['MERKLE_HASH'],   // MERKLE_HASH
          $leaf['CIPHERTEXT'],    // CIPHERTEXT
          $leaf['HMACDIGEST'],    // HMACDIGEST (reuse last HMAC since base_table didn’t store HMAC)
          $nodeType,               // NODE_TYPE placeholder
          $zero,                  // LEFTCHILD
          $leaf['UPLOADTIMESTAMP']// UPLOADTIMESTAMP
        );
        $stmtIns->execute() or die("Leaf insert failed: " . $stmtIns->error);
    }
    $stmtIns->close();

    // Step 3: load back just the newly inserted leaf ROW IDs in order
    $leafRows = [];
    $res2 = $mysqli->query("SELECT TREE_INDEX, MERKLE_HASH 
                            FROM `{$tablename}` 
                            WHERE USER_ID = {$id} 
                              AND NODE_TYPE = 'Leaf'
                            ORDER BY TREE_INDEX")
          or die($mysqli->error);
    while ($r = $res2->fetch_assoc()) {
        $leafRows[] = $r;
    }

    // Step 4: build and insert 1‑level parents
    $stmtPar = $mysqli->prepare("
      INSERT INTO `{$tablename}`
        (USER_ID, FILE_NAME, MERKLE_HASH, NODE_TYPE, LEFTCHILD)
      VALUES (?, ?, ?, 'Parent', ?)
    ");
    $count = count($leafRows);
    for ($i = 0; $i < $count; $i += 2) {
        $left  = $leafRows[$i];
        // if odd‑count, duplicate the last
        $right = ($i + 1 < $count) ? $leafRows[$i+1] : $left;

        // parent hash = H( H_left || H_right )
        $parentHash = hash('sha256', $left['MERKLE_HASH'] . $right['MERKLE_HASH']);

        $parentName = "parent_{$left['TREE_INDEX']}_{$right['TREE_INDEX']}";

        $stmtPar->bind_param(
          "issi",
          $id,
          $parentName,
          $parentHash,
          $left['TREE_INDEX']
        );
        $stmtPar->execute() or die("Parent insert failed: " . $stmtPar->error);
    }
    $stmtPar->close();

}

// LOGOUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Logout_Submit'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Files</title>
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
      width: 450px;
      text-align: center;
    }

    .upload-box h2 {
      margin-bottom: 25px;
      color: #333;
    }

    .upload-box input[type="text"],
    .upload-box textarea {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
    }

    .button {
      display: inline-block;
      margin: 10px 5px;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      text-align: center;
      cursor: pointer;
      text-decoration: none;
      width: 100%;
      box-sizing: border-box;
    }

    .button.primary {
      background-color: #28a745;
      color: white;
    }

    .button.secondary {
      background-color: #007BFF;
      color: white;
    }

    .button.danger {
      background-color: #dc3545;
      color: white;
    }

    .button:hover {
      opacity: 0.9;
    }

    .upload-box form,
    .upload-box a {
      margin-top: 15px;
    }
  </style>
</head>
<body>
  <div class="upload-box">
    <h2>📤 Upload File</h2>

    <!-- Upload Form -->
    <form action="upload.php" method="POST">
      <label for="File_Name">File Name</label>
      <input type="text" id="File_Name" name="File_Name" required>

      <label for="File_Content">Message Content</label>
      <textarea id="File_Content" name="File_Content" rows="6" placeholder="Enter your message here..." required></textarea>

      <input type="submit" class="button primary" name="Upload_Submit" value="Upload">
    </form>

    <!-- Go to File List -->
    <a href="filelist.php" class="button secondary">📁 View Uploaded Files</a>

    <!-- Logout -->
    <form action="upload.php" method="POST">
      <input type="submit" class="button danger" name="Logout_Submit" value="🚪 Logout">
    </form>
  </div>
</body>
</html>
