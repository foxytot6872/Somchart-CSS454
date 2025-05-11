<?php
session_start();
$host = 'localhost';
$db   = 'cloudstorageservice';
$user = 'root';
$pass = 'root';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("DB connection failed: " . $e->getMessage());
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    header("Location: upload.php");
    exit;
  } else {
    $error = "Invalid username or password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
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
      color: #333;
      margin-bottom: 20px;
    }
    .login-box input {
      width: 90%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .login-box input[type="submit"] {
      background: #4CAF50;
      color: white;
      font-size: 16px;
      cursor: pointer;
    }
    .login-box input[type="submit"]:hover {
      background: #45a049;
    }
    .error { color: red; margin-top: 10px; }
    .signup-link {
      margin-top: 15px;
      font-size: 14px;
    }
    .signup-link a {
      color: #007BFF;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Login</h2>
    <form action="" method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="submit" value="Login">
    </form>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <div class="signup-link">Don’t have an account? <a href="signup.php">Sign up</a></div>
  </div>
</body>
</html>
