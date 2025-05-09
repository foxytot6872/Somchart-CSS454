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

    .login-box input[type="submit"]:hover {
      background: #45a049;
    }

    .signup-link {
      margin-top: 15px;
      font-size: 14px;
    }

    .signup-link a {
      color: #007BFF;
      text-decoration: none;
      font-weight: bold;
    }

    .signup-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>Login</h2>
    <form action="login.php" method="POST">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="submit" value="Login">
    </form>
    <div class="signup-link">
      Don't have an account? <a href="signup.html">Sign up</a>
    </div>
  </div>
</body>
</html>
