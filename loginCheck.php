<?php
// Performs login check.
// If logged out or too old cookie (did not use page for more than 15 minutes, then automatic logout)
//
  session_start(); // start up your PHP session! 

  //automatic logout after $sessiontimeout seconds
  $sessiontimeout=60 * 30; // 30 minutes

  //following two lines should prevent from caching pages
//  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
//  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past//
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Cache-Control: no-store, no-cache, must-revalidate');
  header('Cache-Control: post-check=0, pre-check=0', FALSE);
  header('Pragma: no-cache'); 

  //if session is present, check if not too old
  if (isset($_SESSION['isConnected']))
  {
    if (time() - $_SESSION['timestamp']>$sessiontimeout) //15 minutes timeout
    {
      $_POST['passw1']="";
      unset($_SESSION['isConnected']);
      unset($_SESSION['email']);
      unset($_SESSION['permission']);
      unset($_SESSION['timestamp']);
    
      header("Location: index.php");
    }else{
      //make validity of session longer
      $_SESSION['timestamp']=time();
    }
  }
  if (strcasecmp($filename,"index.php")!=0 && (!isset($_SESSION['isConnected']) || !isset($_SESSION['email']) || !isset($_SESSION['permission']) || !isset($_SESSION['timestamp'])))
  {
    header("Location: index.php?error=timeout");
  }
?>
