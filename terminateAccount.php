<?php
  $filename="terminateAccount.php";

  include "loginCheck.php";
  
  include "connectDB.php";

  include "library.php";

  $messageCode=0;
  $message="";
  
  $editmode=0;

  include "header.php";

  echo "<div align=\"left\">";
  echo "<font size=\"4\"><b>Terminate Account</b></font><br>";
  
  if($_SESSION['isConnected']=1){
      
      if(!$_REQUEST['sure']){
         echo "<br /><span style=\"color:red; font-size:100%\">Warning: Do not terminate your account if you are enrolled for credit! (psy100 or psy300)</span><br />";
         
         echo "<br /><span style=\"font-size:100%\">Are you sure you want to terminate your account?</span><br />";
         echo "<br /><div style=\"padding-left:50px;height:30px; float:left;width:100px\"><a href='terminateAccount.php?sure=1'>Yes</div>";
         echo "<div style=\"height:30px; float:left;width:100px\"><a href='aboutme.php'>No</div><br style=\"clear:both\">";
      }
      elseif($_SESSION['permission']=="participant") { 
          mysqli_query("DELETE FROM signsup WHERE participant_email = '".$_SESSION['email']."'");
          mysqli_query("DELETE FROM user WHERE email = '".$_SESSION['email']."'");
          
          
          unset($_SESSION['isConnected']);
          unset($_SESSION['email']);
          unset($_SESSION['permission']);
          unset($_SESSION['timestamp']);         
          echo "<br /><span style=\"color:red; font-size:120%\">Account Terminated</span><br />";
      
      }
      else{
        echo "<br /><span style=\"color:red; font-size:120%\">Admins and researchers can not close their accounts</span><br />";
         
      }
  
  
  }

  

  
 
  include "disconnectDB.php";
  
  if ($messageCode!=0)
  {
    echo "<center><font color=\"red\"><b>".$message."</b></font></center>\n";
  }
  include "footer.php";  
?>
