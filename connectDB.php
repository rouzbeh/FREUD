<?php

  include "databaseParams.php";
  
  $connectionDB=mysqli_connect($servername, $username, $password, $databasename) or die(mysqli_error($connectionDB));
  //mysqli_select_db($databasename);  

?>
