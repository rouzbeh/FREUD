<?php
//
// Performs logout
//
  $indexpage="index.php";

  session_start(); // start up your PHP session! 

  $_POST['passw1']="";
  unset($_SESSION['isConnected']);
  unset($_SESSION['email']);
  unset($_SESSION['permission']);
  unset($_SESSION['timestamp']);
  session_destroy();
  header("Location: ".$indexpage);
?>

