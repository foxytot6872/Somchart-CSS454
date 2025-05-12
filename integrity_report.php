<?php
require_once('Connect.php');
session_start();

if (empty($_SESSION['user_id'])) {
    die("Not logged in.");
}
$userId = $_SESSION['user_id'];
$tablename = $_SESSION['user_table'];

// 1) Fetch files by TREE_INDEX
$stmt = $mysqli->prepare("
    SELECT TREE_INDEX, FILE_ID, FILE_NAME, MERKLE_HASH, CIPHERTEXT
    FROM $tablename
    ORDER BY TREE_INDEX
");
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($r = $result->fetch_assoc()) {
    $rows[] = $r;
}
$stmt->close();

// 2) 1-level Merkle check in pairs
$report = [];
$total = count($rows);
for ($i = 0; $i < $total; $i += 2) {
    if (isset($rows[$i+1])) {
        $a = $rows[$i];
        $b = $rows[$i+1];

        $origParent = hash('sha256', $a['MERKLE_HASH'] . $b['MERKLE_HASH']);
        $newA = hash('sha256', $a['CIPHERTEXT']);
        $newB = hash('sha256', $b['CIPHERTEXT']);
        $newParent = hash('sha256', $newA . $newB);

        if ($origParent === $newParent) {
            $status = '✅ OK';
        } else {
            $statusA = ($a['MERKLE_HASH'] === $newA) ? '✅ OK' : '❌ CORRUPT';
            $statusB = ($b['MERKLE_HASH'] === $newB) ? '✅ OK' : '❌ CORRUPT';
            $status = "Pair mismatch → File {$a['TREE_INDEX']} is $statusA, File {$b['TREE_INDEX']} is $statusB";
        }

        $report[] = [
            'pair' => "{$a['TREE_INDEX']} - {$b['TREE_INDEX']}",
            'status' => $status
        ];
    } else {
        $c = $rows[$i];
        $newC = hash('sha256', $c['CIPHERTEXT']);
        $leafOk = ($c['MERKLE_HASH'] === $newC) ? '✅ OK' : '❌ CORRUPT';
        $report[] = [
            'pair' => "{$c['TREE_INDEX']}",
            'status' => "Single leaf is $leafOk"
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Merkle Integrity Report</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to right, #fddb92, #d1fdff);
      margin: 0;
      padding: 40px;
      display: flex;
      justify-content: center;
    }

    .report-box {
      background-color: white;
      padding: 30px;
      border-radius: 12px;
      width: 90%;
      max-width: 800px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      padding: 12px 18px;
      border: 1px solid #ddd;
      text-align: center;
      font-size: 14px;
    }

    th {
      background-color: #007BFF;
      color: white;
    }

    tr.OK {
      background-color: #e8f5e9;
    }

    tr.CORRUPT {
      background-color: #ffebee;
    }

    .back-btn {
      display: block;
      width: 220px;
      margin: 30px auto 0;
      text-align: center;
      padding: 12px 20px;
      background-color: #6c757d;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-size: 14px;
    }

    .back-btn:hover {
      background-color: #5a6268;
    }
  </style>
</head>
<body>
  <div class="report-box">
    <h2>🧪 Merkle Integrity Report — User #<?= htmlspecialchars($userId) ?></h2>

    <table>
      <tr><th>Tree Index Pair</th><th>Status</th></tr>
      <?php foreach ($report as $row):
        $cls = (strpos($row['status'], 'CORRUPT') !== false) ? 'CORRUPT' : 'OK';
      ?>
      <tr class="<?= $cls ?>">
        <td><?= htmlspecialchars($row['pair']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>

    <a href="filelist.php" class="back-btn">⬅ Back to File List</a>
  </div>
</body>
</html>