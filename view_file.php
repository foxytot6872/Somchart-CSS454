<?php require_once('Connect.php');
session_start();

if (empty($_SESSION['user_key'])) {
  header("Location: login.php");
  exit;
}
$AESkey = $_SESSION["user_key"];
$tablename = $_SESSION['user_table'];

//decrypting files
$q1="SELECT * FROM $tablename WHERE CIPHERTEXT IS NOT NULL";
    $result1=$mysqli->query($q1);
    if(!$result1){
        echo "Insert failed. Error: ".$mysqli->error;
    }
    while($row=$result1->fetch_array()){
        $FileNo = $row['TREE_INDEX']; 
        $Filename = $row['File_Name'];
        $ciphertext_b64 = $row['CIPHERTEXT'];
        $FileHMAChash = $row['HMACDIGEST'];
        $Filetimestamp = $row['UPLOADTIMESTAMP'];
    }
$NewHMAC = hash_hmac('sha256', $ciphertext_b64, $Filetimestamp, false);

if(hash_equals($FileHMAChash, $NewHMAC)){
    $Filecontent = openssl_decrypt(base64_decode($ciphertext_b64), "AES-128-ECB", $AESkey, OPENSSL_RAW_DATA, "");
}
else{
    header("Location: filelist.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Files</title>
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
    <h2>View File</h2>
    <form action="upload.php" method="POST">
      <label>File Name</label>
      <br>
      <label><?php echo $Filename ?></label>
      <br>
      <label>Message</label>
      <br>
      <textarea rows="5" readonly><?php echo $Filecontent ?></textarea>
    </form>

    <br>
    <!-- Button-style link to filelist.html -->
    <a class="gotofilelistbox" href="filelist.php">Go to file list</a>
      
  </div>
</body>
</html>
