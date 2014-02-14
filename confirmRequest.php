<?php
  // Connects to your Database 
  include "connectDB.php";

  $messageCode=0;
  $message="";
  
  if (isset($_GET['val']))
  {
      $query = "SELECT * FROM request WHERE request_id='".$_GET['val']."'";
      $result = mysql_query($query) or die(mysql_error());
      $row = mysql_fetch_array($result, MYSQL_ASSOC);

      $check=mysql_num_rows($result);

      //confirmation code not found
      if ($check != 1) 
      {
        $message = "Your confirmation code not found in database!";
        $messageCode=1;
      }

      //make the last check to make sure, that at least one spot in timeslot is free(it can take some time between sending request email
      // and confirming it by clickig on link and therefore someone can take this free spot)  
      $query1 = "SELECT * FROM request LEFT JOIN timeslot ON timeslot.timeslot_id=request.timeslot_id WHERE request.request_id='".$_GET['val']."'";
      $result1 = mysql_query($query1) or die(mysql_error());
      $row1 = mysql_fetch_array($result1, MYSQL_ASSOC);
  
      $query2 = "SELECT * FROM signsup LEFT JOIN timeslot ON timeslot.timeslot_id=signsup.timeslot_id WHERE timeslot.timeslot_id='".$_GET['val']."'";
      $result2 = mysql_query($query2) or die(mysql_error());
      $num2=mysql_num_rows($result2);
      $capacity_left=($row1['capacity_total']-$num2);
  
      if ($capacity_left<=0)
      {
        $message = "Sorry, this slot is already taken.  Please try another slot if any are still available.";
        $messageCode=2;        
      }

      //if everything is ok, update signsup table and delete request info from request table
      if ($messageCode==0)
      {
        $query1 = "INSERT INTO signsup VALUES ('0', '".$row['request_id']."','".$row['participant_email']."', '".$row['timeslot_id']."')";
        $result1 = mysql_query($query1) or die(mysql_error());

        $query1 = "DELETE FROM request WHERE request_id='".$_GET['val']."'";
        $result1 = mysql_query($query1) or die(mysql_error());


        $message = "You have been successfully registered.  Thank you in advance for your participation.";
        $messageCode=3;
      }
  
  }else{
      $message = "No confirmation code specified!";
      $messageCode=4;
  } 

  include "disconnectDB.php";
  
  if ($messageCode!=0)
  {
    echo $message;
  }
?>
