<?php
  $filename="aboutme.php";

  include "loginCheck.php";
  
  include "connectDB.php";

  include "library.php";

  $messageCode=0;
  $message="";
  
  //This code runs if the form has been submitted
  if (isset($_POST['submit'])) 
  { 
    //This makes sure they did not leave any fields blank
    if (empty($_POST['oldpassword']) || empty($_POST['newpassword1']) || empty($_POST['newpassword2']) ) 
    {
      $message = 'You did not complete all of the required fields';
      $messageCode=1;
    }

    //if all information entered, no empty fields
    if ($messageCode==0)
    {
      $query = "SELECT password FROM user WHERE email = '".$_SESSION['email']."'";
      $result = mysqli_query($connectionDB, $query) or die(mysqli_error($connectionDB));
      $row = mysqli_fetch_array($result, MYSQLI_ASSOC);


      if ($row['password']!=md5($_POST['oldpassword']))
      {
        $message = 'Old password does not match.';
        $messageCode=2;
      }

      if ($_POST['newpassword1']!=$_POST['newpassword2'])
      {
        $message = 'New password does not match.';
        $messageCode=3;
      }
    }

    //save a new password to database
    if ($messageCode==0)
    {
      $query = mysqli_query("UPDATE user SET password='".md5($_POST['newpassword1'])."' WHERE email = '".$_SESSION['email']."'") or die(mysqli_error($connectionDB));

      $message = 'Password changed.';
      $messageCode=4;
    }

  }

  include "header.php";

?>   
    <div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div>
    <font size="3" style="display:block; padding: 25px 10px 10px 0px;"><b>My Experiments</b></font>
   <br />
<?
  echo "<table>\n";
  echo "  <tbody>\n";
  echo "  <tr><th>Experiment title</th><th>Date</th><th>Time</th><th>Location</th><th>Hour/Credit</th></tr>\n";


  $query0 = "SELECT timeslot.timeslot_id, timeslot.edate, timeslot.etime, timeslot.experiment_id FROM signsup LEFT JOIN timeslot ON signsup.timeslot_id=timeslot.timeslot_id WHERE signsup.participant_email='".$_SESSION['email']."'";
  $result0 = mysqli_query($connectionDB, $query0) or die(mysqli_error($connectionDB));
  
  if(mysqli_num_rows($result0)==0) echo "<tr><td colspan='4'>You have not signed up for any experiments so far</td></tr>";
  while($row = mysqli_fetch_array($result0, MYSQLI_ASSOC))
  {
    $query1 = "SELECT title, experiment_id, location, hour_credit FROM experiment WHERE experiment.experiment_id='".$row['experiment_id']."'";
    $result1 = mysqli_query($connectionDB, $query1) or die(mysqli_error($connectionDB));
    $row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC);
    
    echo "  <tr>\n";
   // echo "    <td>".$row['timeslot_id']."</td>\n";
    echo "    <td><a href=\"\" onclick=\"newWindow(0, ".$row1['experiment_id'].")\" >".$row1['title']."</a></td>\n";     
    echo "    <td>".transformDateYearLast($row['edate']).", ".dayofweek($row['edate'])."</td>\n";
    echo "    <td>".time24to12($row['etime'])."</td>\n";
    echo "    <td>".$row1['location']."</td>\n"; 
    echo "    <td>".$row1['hour_credit']."</td>\n"; 
    echo "  </tr>\n";
  }
  
  echo "  </tbody>\n";
  echo "</table>\n";
  
  ?>
  
    <br /><br /><font size="3"style="display:block; padding: 25px 10px 10px 0px;"><b>My Account</b></font><br />

    <!-- Form with text fields etc. -->
    <form action="" method="post">
      <table style="width:auto">
        <tr><th colspan='2'>Change Password</th></tr>
        <tr>
          <td>Old password:</td>
          <td>
            <input type="password" name="oldpassword" maxlength="20" value="">
          </td>
        </tr>
          <td>New password:</td>
          <td>
            <input type="password" name="newpassword1" maxlength="20" value="">
          </td>
        </tr>
          <td>Re-type new password:</td>
          <td>
            <input type="password" name="newpassword2" maxlength="20" value="">
          </td>
        </tr>

        <tr>
          <td colspan=2>
            <input type="submit" name="submit" value="Change">
          </td>
        </tr> 
      </table>
    </form><br /><br />
  
  
  <?php
  
  
  
  if($_SESSION['permission']=="participant"){
  ?>
     <br /><br /><font size="3" style="display:block; padding: 10px 10px 0px 0px;"><b>Terminate Account:</b></font>
<br /><p>If you terminate your account all information will be removed from the system.</p>
<p>To terminate your account click <b><a href="terminateAccount.php">here</a></b> </p>
  <?php
  }        
  include "disconnectDB.php";
  
  if ($messageCode!=0)
  {
    echo "<div class='generalErr'><b>$message</b></div>\n";
  }
  include "footer.php";
?>
