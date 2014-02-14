<?php
  // Connects to your Database 
  include "connectDB.php";

  $messageCode=0;
  $message="";
  
  if (isset($_GET['val']))
  {
      $query = "SELECT * FROM user WHERE validUser='".$_GET['val']."'";
      $result = mysql_query($query) or die(mysql_error());

      $check=mysql_num_rows($result);
  
      //confirmation code not found
      if ($check != 1) 
      {
        $message = "Your confirmation code not found in database!";
        $messageCode=1;
      }

      if ($messageCode==0)
      {
        $query = "UPDATE user SET validUser='0' WHERE validUser='".$_GET['val']."'";
        $result = mysql_query($query) or die(mysql_error());

        $message = "You have been successfully registered.";
        $messageCode=2;
      }
  
  }else{
      $message = "No confirmation code specified!";
      $messageCode=3;
  } 

  include "disconnectDB.php";
  
  include "header.php";
  if ($messageCode!=0)
  {
    echo "<center><font color=\"red\"><b>".$message."</b></font></center>\n";
  }
  
  if ($messageCode==2)
  {
    echo "<br><br><br>\n";
    echo "<center><a href=\"index.php\">Go to login page!</a></center>\n";
  }
  
  include "footer.php";
?>
