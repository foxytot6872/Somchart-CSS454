<?php require_once('Connect.php');

// transition from signup.php
if(isset($_POST['Signup_Submit'])) {
    // Insert data from Signup.php
    $firstname = $_POST['name'];
    $lastname = $_POST['surname'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $username = $_POST['su_username'];
    $password = $_POST['su_password'];
    $AESkey = bin2hex(random_bytes(16));

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Use prepared statements to insert user data
    $stmt = $mysqli->prepare("INSERT INTO users (USER_FIRSTNAME, USER_SURNAME, USER_GENDER, USER_DOB, USERNAME, USER_PASSWORD, USER_KEY) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstname, $lastname, $gender, $dob, $username, $hashed_password, $AESkey);
    
    if ($stmt->execute()) {
        echo "";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();

    $stmt2 = $mysqli->prepare("SELECT USER_ID, USER_PASSWORD FROM users WHERE USERNAME = ?");
    $stmt2->bind_param("s", $username);
    $stmt2->execute();
    $result = $stmt2->get_result();
    if ($result->num_rows > 0) {
        // Fetch user details
        $row = $result->fetch_assoc();
        $id = $row['USER_ID'];
    } else {
        echo "Invalid";
    }
    $stmt2->close();

    $tablename = "USERDB_" . $id;

    $stmt3 = $mysqli->prepare("CREATE TABLE $tablename LIKE BASE_TABLE;");
    if ($stmt3->execute()) {
        echo "";
    } else {
        echo "Error: " . $stmt3->error;
    }
    $stmt3->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Page</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, #74ebd5, #ACB6E5);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-box {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      width: 350px;
      text-align: center;
    }

    .login-box h2 {
      margin-bottom: 30px;
      color: #333;
    }

    .login-box input[type="text"],
    .login-box input[type="password"] {
      width: 90%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
    }

    .login-box input[type="submit"] {
      background: #4CAF50;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s ease;
      font-size: 16px;
    }

    .loginbutton{
      background: #45a049;
      margin-top: 15px;
      display: inline-block;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-size: 20px;
      transition: background 0.3s ease;
    }

    .signup-button {
      margin-top: 15px;
      display: inline-block;
      background-color: #007BFF;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.3s ease;
    }

    .signup-button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Login</h2>
    <form action="upload.php" method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="submit" class="loginbutton" name="Login_Submit" value="Login">
    </form>

    <!-- Button-style link to signup.html -->
    <a class="signup-button" href="signup.php">Sign Up</a>
  </div>
</body>
</html>
