<?
  $filename="forgotlogininfo.php";  
  include "connectDB.php";

  $message="";
  $messageCode=0;

  include "header.php";

  echo "<div align=\"left\">";
  echo "<font size=\"3\"><b>Did you forget your login information?</b></font>";
 
  echo "<br /><br /><p>Make an appointment with an administrator.<p><br>\n";
  echo "</div>";

  if ($messageCode!=0)
  {
    echo "<center><font color=\"red\"><b>".$message."</b></font></center>\n";
  }
  include "footer.php";  
?>
