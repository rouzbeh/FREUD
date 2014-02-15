<?php
  include "databaseParams.php";
  $mysqli = new mysqli($servername, $username, $password, $databasename);
  $connectionDB=mysqli_connect($servername, $username, $password, $databasename) or die(mysqli_error($connectionDB));
?>
