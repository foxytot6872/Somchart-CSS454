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
    SELECT 
      TREE_INDEX AS parent_index,
      FILE_NAME AS parent_name,
      MERKLE_HASH AS parent_hash,
      LEFTCHILD AS left_index
    FROM `{$tablename}`
    WHERE NODE_TYPE = 'Parent'
    ORDER BY TREE_INDEX
");
$stmt->execute();
$parents = $stmt->get_result();
$stmt->close();


$report = [];

foreach ($parents as $node) {
    $pIdx   = $node['parent_index'];
    $pName  = $node['parent_name'];
    $origPH = $node['parent_hash'];
    $lIdx   = $node['left_index'];
    $rIdx   = $lIdx + 1;

    // Check whether TREE_INDEX = $rIdx is really a leaf for this user:
    $chk = $mysqli->prepare("
      SELECT NODE_TYPE
        FROM `{$tablename}`
       WHERE TREE_INDEX = ?
         AND USER_ID   = ?
    ");
    $chk->bind_param("ii", $rIdx, $userId);
    $chk->execute();
    $rowChk = $chk->get_result()->fetch_assoc();
    $chk->close();

    // If it doesn’t exist or isn’t a leaf, duplicate:
    if (! $rowChk || $rowChk['NODE_TYPE'] !== 'Leaf') {
        $rIdx = $lIdx;
    }

    // Fetch leaf data for both children in one go:
    $in = $mysqli->prepare("
      SELECT TREE_INDEX, MERKLE_HASH, CIPHERTEXT
        FROM `{$tablename}`
       WHERE TREE_INDEX IN (?, ?)
         AND USER_ID   = ?
    ");
    $in->bind_param("iii", $lIdx, $rIdx, $userId);
    $in->execute();
    $children = $in->get_result()->fetch_all(MYSQLI_ASSOC);
    $in->close();

    // Map them by TREE_INDEX:
    $map = [];
    foreach ($children as $c) {
        $map[$c['TREE_INDEX']] = $c;
    }

    // Recompute each leaf‐hash (we originally hashed the base64 string):
    $hL = hash('sha256', $map[$lIdx]['CIPHERTEXT']);
    $hR = hash('sha256', $map[$rIdx]['CIPHERTEXT']);

    // New parent:
    $newPH = hash('sha256', $hL . $hR);

    // Compare:
    if ($newPH === $origPH) {
        $status = "✅ {$pName} is OK";
    } else {
        $c1ok = ($map[$lIdx]['MERKLE_HASH'] === $hL)
                ? "✅ File {$lIdx} is OK" : "❌ File {$lIdx} is CORRUPT";
        $c2ok = ($map[$rIdx]['MERKLE_HASH'] === $hR)
                ? "✅ File {$rIdx} is OK" : "❌ File {$rIdx} is CORRUPT";
        $status = "⚠️ File mismatch → {$c1ok}; {$c2ok}";
    }

    $report[] = [
        'children' => ($lIdx === $rIdx)
                       ? "{$lIdx} (duplicated)"
                       : "{$lIdx} & {$rIdx}",
        'status'   => $status
    ];
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
      <tr><th>Node Pair</th><th>Status</th></tr>
      <?php foreach ($report as $row):
        $cls = (strpos($row['status'], 'CORRUPT') !== false) ? 'CORRUPT' : 'OK';
      ?>
      <tr class="<?= $cls ?>">
        <td><?= htmlspecialchars($row['children']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>

    <a href="filelist.php" class="back-btn">⬅ Back to File List</a>
  </div>
</body>
</html>