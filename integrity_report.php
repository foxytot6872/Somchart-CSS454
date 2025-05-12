<?php
//  ——————————————————————————————————————————————
//  1-LEVEL MERKLE CHECK FOR base_table
//  ——————————————————————————————————————————————

require_once('Connect.php');
session_start();

if (empty($_SESSION['user_id'])) {
    die("Not logged in.");
}
$userId = $_SESSION['user_id'];
$tablename = $_SESSION['user_table'];

// 1) Fetch all files for this user, ordered by TREE_INDEX:
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

// 2) Walk the rows in pairs:
$report = [];
$total = count($rows);
for ($i = 0; $i < $total; $i += 2) {
    // If there is a pair (i, i+1)
    if (isset($rows[$i+1])) {
        $a = $rows[$i];
        $b = $rows[$i+1];
        // original parent
        $origParent = hash('sha256', $a['MERKLE_HASH'] . $b['MERKLE_HASH']);
        // recompute leaf-hashes from ciphertext (assuming base64 stored)
        $newA = hash('sha256', base64_decode($a['CIPHERTEXT']));
        $newB = hash('sha256', base64_decode($b['CIPHERTEXT']));
        $newParent = hash('sha256', $newA . $newB);

        if ($origParent === $newParent) {
            $status = 'OK';
        } else {
            // fallback: check each leaf individually
            $statusA = ($a['MERKLE_HASH'] === $newA) ? 'OK' : 'CORRUPT';
            $statusB = ($b['MERKLE_HASH'] === $newB) ? 'OK' : 'CORRUPT';
            $status = "Pair mismatch → File {$a['TREE_INDEX']} is $statusA, File {$b['TREE_INDEX']} is $statusB";
        }

        $report[] = [
            'pair'   => "{$a['TREE_INDEX']}-{$b['TREE_INDEX']}",
            'status' => $status
        ];

    } else {
        // odd one out: only $rows[$i]
        $c = $rows[$i];
        $newC = hash('sha256', base64_decode($c['CIPHERTEXT']));
        $leafOk = ($c['MERKLE_HASH'] === $newC) ? 'OK' : 'CORRUPT';
        $report[] = [
            'pair'   => "{$c['TREE_INDEX']}",
            'status' => "Single leaf is $leafOk"
        ];
    }
}

// 3) Output a simple HTML report:
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Merkle Integrity Report</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 60%; margin: auto; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f4f4f4; }
    .OK      { background: #c8e6c9; }
    .CORRUPT { background: #ffcdd2; }
  </style>
</head>
<body>
  <h2>File Integrity Report for User #<?= htmlspecialchars($userId) ?></h2>
  <table>
    <tr><th>Tree Index Pair</th><th>Status</th></tr>
    <?php foreach ($report as $row): 
      $cls = (strpos($row['status'], 'OK') !== false && strpos($row['status'], 'CORRUPT') === false)
             ? 'OK' : 'CORRUPT';
    ?>
      <tr class="<?= $cls ?>">
        <td><?= htmlspecialchars($row['pair']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>