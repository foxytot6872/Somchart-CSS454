<?php require_once('Connect.php');
session_start();

if (empty($_SESSION['user_key'])) {
  header("Location: login.php");
  exit;
}
$AESkey = $_SESSION["user_key"];
$tablename = $_SESSION['user_table'];

?>
