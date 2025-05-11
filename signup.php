<?php
$host = 'localhost';
$db   = 'cloudstorageservice';
$user = 'root';
$pass = 'root';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("DB error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST['name']);
  $surname = trim($_POST['surname']);
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $gender = $_POST['gender'];
  $dob = $_POST['dob'];
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $check->execute([$username]);
  if ($check->rowCount() > 0) {
    $error = "Username already exists.";
  } else {
    $stmt = $pdo->prepare("INSERT INTO users (name, surname, username, password, gender, dob)
                          VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $surname, $username, $hashedPassword, $gender, $dob])) {
      header("Location: login.php");
      exit;
    } else {
      $error = "Signup failed.";
    }
  }
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
    .message { margin-top: 10px; }
    .error { color: red; }
  </style>
</head>
<body>
  <div class="signup-box">
    <h2>Create Account</h2>
    <form action="" method="POST">
      <input type="text" name="name" placeholder="Name" required>
      <input type="text" name="surname" placeholder="Surname" required>
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="gender" required>
        <option value="" disabled selected>Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
      </select>
      <input type="date" name="dob" required>
      <input type="submit" value="Sign Up">
      <div class="message">Already have an account? <a href="login.php">Login here</a></div>
    </form>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
  </div>
</body>
</html>
