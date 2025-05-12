<?php
require_once('Connect.php');
session_start();

// Check required session and GET variables
if (empty($_SESSION['user_key']) || empty($_SESSION['user_table']) || empty($_GET['fid'])) {
    die("Unauthorized access or missing file ID.");
}

$AESkey = $_SESSION["user_key"];
$tablename = $_SESSION["user_table"];
$fileId = intval($_GET['fid']);

// Fetch the file from DB by FILE_ID
$stmt = $mysqli->prepare("SELECT FILE_NAME, CIPHERTEXT, HMACDIGEST, UPLOADTIMESTAMP FROM `$tablename` WHERE FILE_ID = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("File not found.");
}

$row = $result->fetch_assoc();
$filename = $row["FILE_NAME"];
$ciphertext_b64 = $row["CIPHERTEXT"];
$hmac = $row["HMACDIGEST"];
$timestamp = $row["UPLOADTIMESTAMP"];

// Verify HMAC
$generated_hmac = hash_hmac('sha256', $ciphertext_b64, $timestamp);
if (!hash_equals($hmac, $generated_hmac)) {
    die("File integrity check failed.");
}

// Decrypt content
$plaintext = openssl_decrypt(base64_decode($ciphertext_b64), "AES-128-ECB", $AESkey, OPENSSL_RAW_DATA);

// Output as a downloadable .txt file
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . basename($filename) . '.txt"');
header('Content-Length: ' . strlen($plaintext));
echo $plaintext;
exit;
?>
