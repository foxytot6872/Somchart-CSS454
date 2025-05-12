<?php require_once('Connect.php');
session_start();

//transition from login.php
if (isset($_POST['Login_Submit'])) {
    // Insert data from Login.php
    $username = $_POST['username'];
    $passwd = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($passwd)) {
        echo "Username and password cannot be empty.";
        exit;
    }

    // Use prepared statements to fetch the hashed password
    $stmt = $mysqli->prepare("SELECT USER_ID, USER_PASSWORD, USER_KEY FROM users WHERE USERNAME = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user details
        $row = $result->fetch_assoc();
        $hashed_password = $row['USER_PASSWORD'];
        $id = $row['USER_ID'];
        $AESkey = $row['USER_KEY'];      
        $tablename = "userdb_" . $id;  

        // Verify the password
        if (password_verify($passwd, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["user_key"] = $AESkey;
            $_SESSION["user_table"] = $tablename;

        } else {
            echo "Invalid username or password.";
            session_destroy();
            header("Location: login.php");
            exit;
        }
    } else {
        echo "Invalid username or password.";
        session_destroy();
        header("Location: login.php");
        exit;
    }

    $stmt->close();
}


//upload file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Upload_Submit'])) {
  $id = $_SESSION["user_id"];
  $AESkey = $_SESSION["user_key"];
  $tablename = $_SESSION["user_table"];

  $Filename = $_POST['File_Name'];
  $Filecontent = $_POST['File_Content'];
  $Filetimestamp = date('Y-m-d H:i:s');

  $ciphertext = openssl_encrypt($Filecontent, "AES-128-ECB", $AESkey, OPENSSL_RAW_DATA, "");
  $ciphertext_b64 = base64_encode($ciphertext);
  $Filehash = hash('sha256', $ciphertext_b64, false);
  $FileHMAChash = hash_hmac('sha256', $ciphertext_b64, $Filetimestamp, false);

  $q = "INSERT INTO all_file (FILE_NAME, USER_ID, CIPHERTEXT, HMACDIGEST) VALUES ('$Filename', '$id', '$ciphertext_b64', '$FileHMAChash');";
  $mysqli->query($q) or die($mysqli->error);
  $Fileid = $mysqli->insert_id;

  $q2 = "INSERT INTO $tablename (FILE_ID, USER_ID, FILE_NAME, MERKLE_HASH, CIPHERTEXT, HMACDIGEST, UPLOADTIMESTAMP) VALUES ('$Fileid', '$id', '$Filename', '$Filehash', '$ciphertext_b64', '$FileHMAChash', '$Filetimestamp');";
  $result2=$mysqli->query($q2);
  if(!$result2){
        echo "Insert failed. Error: ".$mysqli->error;
  }
}



// logout

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

    .upload-box input[type="file"] {
      display: block;
      margin: 20px auto;
      font-size: 14px;
    }

    .uploadbox{
      background-color: #28a745;
      color: white;
      padding: 10px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s;
    }

    .gotofilelistbox{
      background-color: #ACB6E5;
      color: white;
      padding: 10px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 12px;
      transition: background 0.3s;
    }

    .logoutbox{
      background-color:rgb(0, 0, 0);
      color: white;
      padding: 10px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 12px;
      transition: background 0.3s;
    }

    .upload-box input[type="submit"]:hover {
      background-color: #218838;
    }

    .message {
      margin-top: 15px;
      font-size: 14px;
    }

    .success {
      color: green;
    }

    .error {
      color: red;
    }
  </style>
</head>
<body>
  <div class="upload-box">
    <h2>Upload Files</h2>
    <form action="upload.php" method="POST">
      <label>File Name</label>
      <input type="text" name='File_Name' placeholder="" required>
      <label>Message</label>
      <textarea name='File_Content' rows="5"></textarea>
      <input type="submit" class="uploadbox" name="Upload_Submit" value="Upload">
    </form>

    <!-- Button-style link to filelist.html -->
    <a class="gotofilelistbox" href="filelist.php">Go to file list</a>
      
    <form action="upload.php" method="POST">
      <input type="submit" class="logoutbox" name="Logout_Submit" value="Logout">
    </form>
  </div>
</body>
</html>
