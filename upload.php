<?php require_once('Connect.php');

//transition from login.php
if (isset($_POST['Login_Submit'])) {
    session_start();

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

        // Verify the password
        if (password_verify($passwd, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["user_key"] = $AESkey;

        } else {
            echo "Invalid username or password.";
            header("Location: login.php");
        }
    } else {
        echo "Invalid username or password.";
        header("Location: login.php");
    }

    $stmt->close();
}

/*
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_files'])) {
    $host = 'localhost';
    $db   = 'your_database';
    $user = 'your_db_user';
    $pass = 'your_db_password';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $total = count($_FILES['uploaded_files']['name']);
        $successCount = 0;

        for ($i = 0; $i < $total; $i++) {
            if ($_FILES['uploaded_files']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['uploaded_files']['name'][$i];
                $fileTmp = $_FILES['uploaded_files']['tmp_name'][$i];
                $fileType = $_FILES['uploaded_files']['type'][$i];
                $fileData = file_get_contents($fileTmp);

                $stmt = $pdo->prepare("INSERT INTO uploads (filename, filetype, filedata, upload_time) VALUES (?, ?, ?, NOW())");
                if ($stmt->execute([$fileName, $fileType, $fileData])) {
                    $successCount++;
                }
            }
        }

        // Redirect to list page after upload
        header("Location: file_list.php?uploaded=$successCount");
        exit;

    } catch (PDOException $e) {
        $message = "<div class='message error'>Database error: " . $e->getMessage() . "</div>";
    }
}
    */
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
    <form action="" method="POST" enctype="multipart/form-data">
      <input type="file" name="uploaded_files[]" multiple required>
      <a class="uploadbox" href="filelist.php">UPLOAD</a>
    </form>
  </div>
</body>
</html>
