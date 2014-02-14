<?php

  include "databaseParams.php";
  
  $connectionDB=mysql_connect($servername, $username, $password) or die(mysql_error());
  mysql_select_db($databasename);  

?>
