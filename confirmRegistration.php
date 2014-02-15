<?php
// Connects to your Database 
include "connectDB.php";

$messageCode=0;
$message="";
  
if (isset($_GET['val']))
{
  $stmt = $mysqli->prepare("SELECT * FROM user WHERE validUser=?");
  if(!$stmt) die("Prepare failed.");
      
  $stmt->bind_param('s',$_GET['val']);
  $stmt->execute();
  $stmt->bind_result($result);
  $check=mysqli_num_rows($result);
  
  //confirmation code not found
  if ($check != 1) 
  {
    $message = "Your confirmation code not found in database!";
    $messageCode=1;
  }

  if ($messageCode==0)
  {
    $stmt = $mysqli->prepare("UPDATE user SET validUser='0' WHERE validUser=?");
    if(!$stmt) die("Prepare failed.");
      
    $stmt->bind_param('s',$_GET['val']);
    $stmt->execute();
    $stmt->bind_result($result);

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
