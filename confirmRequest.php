<?php
// Connects to your Database 
include "connectDB.php";

$messageCode=0;
$message="";
  
if (isset($_GET['val']))
{
      
  $stmt = $mysqli->prepare("SELECT * FROM request WHERE request_id=?");
  if(!$stmt) die("Prepare failed");
  $stmt->bind_param('i', $_GET['val']);
  if (!$stmt->execute()) {
    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $result = $stmt->get_result();
  $stmt->close();
  $row = $result->fetch_assoc();
  $check=mysqli_num_rows($result);

  //confirmation code not found
  if ($check != 1) 
  {
    $message = "Your confirmation code not found in database!";
    $messageCode=1;
  }

  //make the last check to make sure, that at least one spot in timeslot is free(it can take some time between sending request email
  // and confirming it by clickig on link and therefore someone can take this free spot)  
  $stmt = $mysqli->prepare("SELECT * FROM request LEFT JOIN timeslot ON timeslot.timeslot_id=request.timeslot_id WHERE request.request_id=?");
  if(!$stmt) die("Prepare failed");
  $stmt->bind_param('i', $_GET['val']);
  if (!$stmt->execute()) {
    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $result1=$stmt->get_result();
  $stmt->close();
  $row1 = $result1->fetch_assoc();
  
  $stmt = $mysqli->prepare("SELECT * FROM signsup LEFT JOIN timeslot ON timeslot.timeslot_id=signsup.timeslot_id WHERE timeslot.timeslot_id=?");
  if(!$stmt) die("Prepare failed");
  $stmt->bind_param('i', $_GET['val']);
  if (!$stmt->execute()) {
    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $result2 = $stmt->get_result();
  $stmt->close();
  $num2=mysqli_num_rows($result2);
  $capacity_left=($row1['capacity_total']-$num2);
  
  if ($capacity_left<=0)
  {
    $message = "Sorry, this slot is already taken.  Please try another slot if any are still available.";
    $messageCode=2;        
  }

  //if everything is ok, update signsup table and delete request info from request table
  if ($messageCode==0)
  {
    $stmt = $mysqli->prepare("INSERT INTO signsup VALUES ('0', ?, ?, ?)");
    if(!$stmt) die("Prepare failed");
    $stmt->bind_param('isi', $row['request_id'], $row['participant_email'], $row['timeslot_id']);
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    $stmt->close();

    $stmt = $mysqli->prepare("DELETE FROM request WHERE request_id=?");
    if(!$stmt) die("Prepare failed");
    $stmt->bind_param('i', $_GET['val']);
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    $stmt->close();

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
