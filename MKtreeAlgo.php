<?php
require_once('Connect.php');
session_start();

if (!isset($_SESSION["user_table"])) {
    echo "User session not found. Please login.";
    exit;
}

$tablename = $_SESSION["user_table"];

/**
 * SHA-256 wrapper
 */
function sha256($data) {
    return hash('sha256', $data);
}

/**
 * Builds a Merkle Tree from database and returns:
 * - Merkle Root
 * - All tree levels
 */
function build_merkle_tree_from_db($mysqli, $table_name) {
    $query = "SELECT MERKLE_HASH FROM `$table_name` ORDER BY TREE_INDEX ASC";
    $result = $mysqli->query($query);

    if (!$result || $result->num_rows == 0) {
        return [null, []];
    }

    $levels = [];
    $current_level = [];

    while ($row = $result->fetch_assoc()) {
        $current_level[] = $row['MERKLE_HASH'];
    }

    $levels[] = $current_level;

    while (count($current_level) > 1) {
        $next_level = [];
        for ($i = 0; $i < count($current_level); $i += 2) {
            $left = $current_level[$i];
            $right = ($i + 1 < count($current_level)) ? $current_level[$i + 1] : $left;
            $next_level[] = sha256($left . $right);
        }
        $levels[] = $next_level;
        $current_level = $next_level;
    }

    return [end($levels)[0], $levels];
}

list($merkle_root, $levels) = build_merkle_tree_from_db($mysqli, $tablename);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Merkle Tree Builder</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, #fceabb, #f8b500);
      margin: 0;
      padding: 40px;
      display: flex;
      justify-content: center;
    }

    .container {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 1000px;
    }

    h2, h3 {
      text-align: center;
      color: #333;
    }

    .root {
      font-size: 13px;
      word-break: break-word;
      background-color: #f1f1f1;
      padding: 15px;
      border-radius: 10px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
    }

    .level {
      margin-bottom: 30px;
    }

    .level strong {
      display: block;
      margin-bottom: 10px;
      color: #007BFF;
    }

    .hash-box {
      display: inline-block;
      margin: 5px;
      padding: 10px 14px;
      background: #f9f9f9;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 12px;
      max-width: 280px;
      word-break: break-word;
    }

    .back-button {
      display: inline-block;
      margin-top: 30px;
      padding: 12px 24px;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 8px;
    }

    .back-button:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>🔐 Merkle Tree Construction</h2>

    <?php if ($merkle_root): ?>
      <p><strong>Merkle Root:</strong></p>
      <div class="root"><?= htmlspecialchars($merkle_root) ?></div>
    <?php else: ?>
      <p style="text-align:center;">No Merkle hashes found in the database. Tree cannot be built.</p>
    <?php endif; ?>

    <?php if (!empty($levels)): ?>
      <h3>🌲 Merkle Tree Levels</h3>
      <?php
      $level_num = 0;
      foreach ($levels as $level) {
          echo "<div class='level'>";
          echo "<strong>Level $level_num:</strong>";
          foreach ($level as $hash) {
              echo "<div class='hash-box'>" . htmlspecialchars($hash) . "</div>";
          }
          echo "</div>";
          $level_num++;
      }
      ?>
    <?php endif; ?>

    <div style="text-align:center;">
      <a class="back-button" href="upload.php">⬅ Back to Upload</a>
    </div>
  </div>
</body>
</html>
