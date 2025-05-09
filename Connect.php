<?php
$mysqli = new mysqli('localhost','root','root','cloudstorageservice');
   if($mysqli->connect_errno){
      echo $mysqli->connect_errno.": ".$mysqli->connect_error;
   }
?>