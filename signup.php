<?php
// CONNECT TO DB
$host = 'localhost';
$db   = 'cloudstorageservice';
$user = 'root';
$pass = ''; // Change if needed

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$success = "";
$error = "";

// SIGNUP LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Signup_Submit'])) {
    $firstname = $_POST['name'];
    $lastname = $_POST['surname'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $username = $_POST['su_username'];
    $password = $_POST['su_password'];

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $mysqli->prepare("INSERT INTO user (USER_FIRSTNAME, USER_SURNAME, USER_GENDER, USER_DOB, USERNAME, USER_PASSWORD) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdss", $firstname, $lastname, $gender, $dob, $username, $hashed_password);

    if ($stmt->execute()) {
        $success = "Sign up successful. You can now <a href='login.php'>Login</a>";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <style>
    body {
      background: linear-gradient(to right, #ffecd2, #fcb69f);
      font-family: "Segoe UI", sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .signup-box {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      width: 400px;
      text-align: center;
    }
    .signup-box h2 {
      margin-bottom: 20px;
      color: #333;
    }
    .signup-box input, .signup-box select {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
    }
    .signup-box input[type="submit"] {
      background-color: #007BFF;
      color: white;
      padding: 15px 25px;
      font-size: 18px;
      cursor: pointer;
      margin-top: 15px;
    }
    .signup-box input[type="submit"]:hover {
      background-color: #0056b3;
    }
    .message { margin-top: 15px; }
    .success { color: green; }
    .error { color: red; }
  </style>
</head>
<body>
  <div class="signup-box">
    <h2>Create Account</h2>
    <form action="" method="POST">
      <input type="text" name="name" placeholder="Name" required>
      <input type="text" name="surname" placeholder="Surname" required>
      <input type="text" name="su_username" placeholder="Username" required>
      <input type="password" name="su_password" placeholder="Password" required>
      <select name="gender" required>
        <option value="" disabled selected>Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
      </select>
      <input type="date" name="dob" required>
      <input type="submit" name="Signup_Submit" value="Sign Up">
    </form>
    <div class="message">
      Already have an account? <a href="login.php">Login</a>
    </div>
    <?php
      if ($success) echo "<div class='success'>$success</div>";
      if ($error) echo "<div class='error'>$error</div>";
    ?>
  </div>
</body>
</html>
