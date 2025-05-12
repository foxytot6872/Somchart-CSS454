<?php require_once('Connect.php');
session_start();

if (empty($_SESSION['user_table'])) {
  header("Location: login.php");
  exit;
}
$tablename = $_SESSION['user_table'];


?>