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
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      width: 400px;
    }

    .signup-box h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }

    .signup-box input[type="text"],
    .signup-box input[type="password"],
    .signup-box input[type="date"],
    .signup-box select {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      box-sizing: border-box;
    }

    .signup-box input[type="submit"] {
      background-color: #4CAF50;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
    }

    .signup{
      margin-top: 15px;
      display: inline-block;
      background-color: #007BFF;
      color: white;
      padding: 10px 20px;
      border: center;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.3s ease;
    }

    .message {
      margin-top: 15px;
      text-align: center;
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
  <div class="signup-box">
    <h2>Create Account</h2>
    <form action="login.php" method="POST">
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
      <input type="submit" name="Signup_Submit"  class="signup " value="Sign up">
    </form>
  </div>
</body>
</html>
