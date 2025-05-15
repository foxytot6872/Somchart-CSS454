<?php
require_once('Connect.php');
session_start();

if (empty($_SESSION['user_id']) || empty($_SESSION['user_table'])) {
    die("Unauthorized");
}

$userid = $_SESSION['user_id'];
$tablename = $_SESSION['user_table'];

$searchName = '';
$results = [];
$message = '';

if (isset($_POST['search'])) {
    $searchName = trim($_POST['filename']);
    $safeSearch = '%' . $mysqli->real_escape_string($searchName) . '%';

    // Simulate bloom filter
    $hash = crc32($searchName) % 1000;
    $bloom = [];

    $res = $mysqli->query("SELECT FILE_NAME FROM `$tablename`");
    while ($r = $res->fetch_assoc()) {
        $bloom[crc32($r['FILE_NAME']) % 1000] = true;
    }

    if (!isset($bloom[$hash])) {
        $message = "❌ File name mismatch";
    } else {
        $stmt = $mysqli->prepare("SELECT FILE_ID, FILE_NAME FROM `$tablename` WHERE FILE_NAME LIKE ?");
        $stmt->bind_param("s", $safeSearch);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($results)) {
            $message = "❌ File name mismatch (false positive)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Retrieve File</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, #e0c3fc, #8ec5fc);
      padding: 60px;
    }
    .container {
      background: white;
      padding: 30px;
      max-width: 600px;
      margin: auto;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.15);
      text-align: center;
    }
    input[type="text"] {
      padding: 10px;
      width: 80%;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 16px;
    }
    .search-btn {
      padding: 10px 20px;
      font-size: 15px;
      border: none;
      background-color: #007BFF;
      color: white;
      border-radius: 6px;
      cursor: pointer;
    }
    .search-btn:hover {
      background-color: #0056b3;
    }
    .result-box {
      background: #f4f4f4;
      padding: 20px;
      border-radius: 8px;
      margin-top: 20px;
      text-align: left;
    }
    .download-link {
      display: inline-block;
      margin-top: 8px;
      padding: 8px 12px;
      background-color: #28a745;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
    }
    .download-link:hover {
      background-color: #218838;
    }
    .message {
      color: red;
      margin-top: 20px;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>🔍 Search and Download Your Files</h2>

    <form method="POST">
      <input type="text" name="filename" placeholder="Enter file name..." value="<?= htmlspecialchars($searchName) ?>" required>
      <button type="submit" name="search" class="search-btn">Search</button>
    </form>

   <?php if (!empty($results)): ?>
  <div class="result-box">
    <h3>📄 Matching Files:</h3>
    <ul style="list-style: none; padding: 0;">
      <?php foreach ($results as $file): ?>
        <li style="margin-bottom: 12px;">
          <strong><?= htmlspecialchars($file['FILE_NAME']) ?></strong><br>
          <a class="download-link" href="download_file.php?fid=<?= $file['FILE_ID'] ?>">⬇ Retrieve File</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php elseif ($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<!-- 🔙 Back button -->
<form action="filelist.php" method="get" style="margin-top: 30px;">
  <button type="submit" class="search-btn" style="background-color: #6c757d;">🔙 Back to My Files</button>
</form>

  </div>

</body>
</html>
