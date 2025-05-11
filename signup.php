<?php
// signup.php
$host = 'localhost';
$db   = 'cloudstorageservice'; // match your SQL file
$user = 'root';                // change if needed
$pass = 'root';                    // change if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name     = trim($_POST['name']);
    $surname  = trim($_POST['surname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $gender   = $_POST['gender'];
    $dob      = $_POST['dob'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        echo "Username already exists. Try another.";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, surname, username, password, gender, dob)
                           VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $surname, $username, $hashedPassword, $gender, $dob])) {
        header("Location: login.php");
        exit;
    } else {
        echo "Signup failed.";
    }
}
?>
