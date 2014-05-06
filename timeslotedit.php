<?php
$filename="timeslotedit.php";
require("require.php");
include "loginCheck.php";

include "connectDB.php";

include "library.php";

include "helper.php";
if(isset($_SESSION['permission']) && ($_SESSION['permission']=="admin" || $_SESSION['permission']=="researcher")){
  $messageCode=0;
  $message="";

  $rememberFormValues=0;
  $editmode=0;
  $original_date = $_POST['edate'];

  //sendMailToUser(5);

  if (isset($_POST['reset']) )
  {
    header("Location: ".$filename);
  }

  //This code runs if the form has been submitted
  if (isset($_POST['submit']) )
  {
    //to remember correct number of timeslots, when not all required fields filled in correctly
    if (isset($_POST['id']) )
    {
      $iimax=$_POST['id'];
    }else{
      $iimax=1;
    }

    //This makes sure they did not leave any fields blank
    if (empty($_POST['experiment_id']) || empty($_POST['edate']) ) 
    {
      $message = 'You did not complete all of the required fields';
      $messageCode=1;
    }

    if ($messageCode==0)
    {
      $_POST['edate'] = transformDateYearFirst($_POST['edate']);
      for ($ii=1; $ii<=$iimax;$ii++)
      {
        $hour="hour".$ii;
        $minute="mimute".$ii;
        $daypart="daypart".$ii;
        $capacity="capacity".$ii;

        $etime1=time12to24($_POST[$hour], $_POST[$minute], $_POST[$daypart]);

        //now we insert it into the database 
        if (!($stmt = $mysqli->prepare("INSERT INTO timeslot VALUES ('0', ?, ?, ?, '0', ?)"))) {
          echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        $stmt->bind_param("issi", $_POST['experiment_id'], $_POST['edate'], $etime1, $_POST[$capacity]);
        if (!$stmt->execute()) {
          echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        $result = $stmt->get_result();
        $stmt->close();
      }  
      //Print information about success of creation
      $message="Timeslot(s) created!";
      $messageCode=2;
    }

  }

  // action=0 ... remove timeslot
  // action=1 ... show user in a selected timeslot //see body section
  // action=2 ... remove user from timeslot

  if (isset($_GET['action']) && isset($_GET['id']))
  {
    switch($_GET['action'])
    {
      //remove timeslot from DB
      case 0:
        {
          sendMailToParticipants($_GET['id']); // notify all signed user about it
          
          if (!($stmt = $mysqli->prepare("DELETE FROM timeslot WHERE timeslot_id=?"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
          $stmt->bind_param("i", $_GET['id']);
          if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
          $result = $stmt->get_result();
          $stmt->close();
          header("Location: ".$filename."?experiment_id=".$_REQUEST['experiment_id']);
          break;
        }
        // delete already confirmed signup
      case 2:
        {
          sendMailToUser($_GET['id']); // notify user about it 
          if (!($stmt = $mysqli->prepare("DELETE FROM signsup WHERE sign_up_id=?"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
          $stmt->bind_param("i", $_GET['id']);
          if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
          $result1 = $stmt->get_result();
          $stmt->close();
          break;       
        } 
    }
  }

  //add a slot 
  if (isset($_REQUEST['addslot']) )
  {
    //echo "je tu";
    $rememberFormValues=1;
    $iimax=$_POST['id']+1;
  }
  //remove a slot
  if (isset($_POST['removeslot']) )
  {
    $rememberFormValues=1;
    $iimax=$_POST['id']-1;
  }

  include("header.php");


  if(isset($_GET['archive'])){
    $title = 'Archive of timeslots';
    $que = "DATE_SUB(CURDATE(),INTERVAL 1 DAY) > timeslot.edate AND";
    $text = false;
    $pre = '?archive&amp;';
    $back = 'timeslotedit.php';
  }else{
    $title = 'List of recent timeslots';
    $que = "DATE_SUB(CURDATE(),INTERVAL 1 DAY) <= timeslot.edate AND";
    $text = true;
    $pre = '?';
    $back = 'index1.php';
  }

  echo "<div class='goBackTop'>&lt; <a href='".$back."'>Back</a></div>";

  echo "<font size='3' style='display:block; padding: 25px 10px 10px 0px;'><b>$title</b></font>";

  //update may 2009

  if ($text) echo '<div class="petrblock">Old timeslots can be found in the <a href="timeslotedit.php?archive">ARCHIVE</a></div>';

  //generate table with all the data
  echo "<table>\n";
  echo "  <tbody>\n";
  echo "    <tr>\n";
  echo "      <th>Timeslot ID</th><th>Experiment Title</th><th>Date</th><th>Time</th><th>Total Seats</th><th>Available</th><th>Participants</th><th>Remove</th>\n";
  echo "    </tr>\n";

  if (!($stmt = $mysqli->prepare("SELECT timeslot.*, experiment.title, experiment.researcher_email, 
		( timeslot.capacity_total - 
		  (SELECT count(*) from signsup 
		   WHERE signsup.timeslot_id=timeslot.timeslot_id ) 
		) as available 
		FROM experiment 
		inner JOIN timeslot ON experiment.experiment_id=timeslot.experiment_id 
		WHERE $que experiment.researcher_email=? 
		ORDER BY timeslot.edate ASC, timeslot.etime ASC"))) {
    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $stmt->bind_param("s",$_SESSION['email']);
  if (!$stmt->execute()) {
    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $result = $stmt->get_result();
  $counter=0;
  if(mysqli_num_rows($result)==0) echo "<tr><td colspan='7'>No timeslots available</td></tr>";
  while($row = $result->fetch_assoc())
  {
    if(($counter++)%2==0)  echo "  <tr class='sudy'>\n";
    else                   echo "  <tr class='lichy'>\n";
    echo "    <td>".$row['timeslot_id']."</td>\n";
    echo "    <td>".$row['title']."</td>\n";
    echo "    <td>".transformDateYearLast($row['edate']).", ".dayofweek($row['edate'])."</td>\n";
    echo "    <td>".time24to12($row['etime'])."</td>\n";
    echo "    <td>".$row['capacity_total']."</td>\n";
    echo "    <td>".$row['available']."</td>\n";
    echo "    <td><a href=\"".$pre."id=".$row['timeslot_id']."&action=1#users\"><img class='noborder' src='images/users_icon.gif' alt='Show Participants' title='Show Participants' height='25' width='31'></a></td>\n";
    echo "    <td><a onclick=\"return confirm('Are you sure you want to delete this timeslot? Doing this will allow any participant in this slot to register for your study again!');\" href=\"".$pre."id=".$row['timeslot_id']."&action=0\"><img class='noborder' src='images/delete_icon.gif' alt='Remove Timeslot' title='Remove Timeslot' height='15' width='15'></a></td>\n";
    echo "  </tr>\n";
  }
  $stmt->close();
  echo "  </tbody>\n";
  echo "</table>\n";


  // action=0 ... remove timeslot       //must be before html tag to enable sending header for reloading page
  // action=1 ... show user in a selected timeslot
  // action=2 ... remove user from timeslot  
  if (isset($_GET['action']) && isset($_GET['id']))
  {
    switch($_GET['action'])
    {
      case 1:
        {
          if (!($stmt = $mysqli->prepare("SELECT * FROM signsup LEFT JOIN timeslot ON signsup.timeslot_id=timeslot.timeslot_id WHERE signsup.timeslot_id=?"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
          $stmt->bind_param("i",$_GET['id']);
          if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
          $result = $stmt->get_result();
          $stmt->close();
          //generate table with all the data
          echo "<br /><br /><br /><p><div id='users' align=\"left\">";
          echo "<font size=\"3\"><b>List of all users signed in timeslot ".$_GET['id']."</b></font><br /><br />";
          echo "</div></p>";

          echo "<table style='width:auto'>\n";
          echo "  <tbody>\n";
          echo "    <tr>\n";
          echo "      <th>Participant Email</th><th>Remove From Timeslot</th>\n";
          echo "    </tr>\n"; 

          $counter2 = 0;  
          if(mysqli_num_rows($result)==0) echo "<tr><td colspan='2'>No participants found</td></tr>";
          while($row = $result->fetch_assoc())
          {        
            if(($counter2++)%2==0)  echo "  <tr class='sudy'>\n";
            else                   echo "  <tr class='lichy'>\n";
            echo "      <td>".$row['participant_email']."</td><td><a href=\"".$pre."id=".$row['sign_up_id']."&action=2\"><img onclick=\"return confirm('Are you sure you want to remove this participant?');\" class='noborder' src='images/delete_icon.gif' alt='Remove Participant' title='Remove Participant' height='15' width='15'></a></td>\n";
            echo "    </tr>\n";
          }

          echo "  </tbody>\n";
          echo "</table>\n";

          break;
        }

    }
  }
  if (!isset($iimax) || $iimax<=0) {$iimax=1;}

  if ($rememberFormValues==0){$rememberFormValues=$messageCode;}
  //echo $_POST['edate'];
  echo "<br /><br /><br /><p><div align=\"left\">";
  echo "<font size=\"3\"><b>Create a new timeslot(s)</b></font>";
  echo "</div></p><br />";
  echo '<!-- Form with text fields etc. -->';
  //find all experiments lead by user with currently logged researcher
  if (!($stmt = $mysqli->prepare("SELECT experiment_id, title FROM user inner JOIN experiment ON user.email=experiment.researcher_email WHERE email=?"))) {
    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $stmt->bind_param("s", $_SESSION['email']);
  if (!$stmt->execute()) {
    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $result = $stmt->get_result();
  $num =  mysqli_num_rows($result);
    
  if($num==0) echo 'You have no experiment assigned';
  else {

?>

    <form name="data" action="<?php echo $filename;?>" method="post">
    <label>Experiment:</label>
    <select name="experiment_id">
<?php

    while($row = $result->fetch_assoc())
    {
      echo "        <option value=\"".$row['experiment_id']."\"";
      if ($rememberFormValues!=0 && isset($_POST['experiment_id']) && $_POST['experiment_id']==$row['experiment_id']){echo " selected=\"yes\" ";}
      echo " >".$row['title']."</option>\n";          
    }
?>
    </select>
    <label>Date <small>(mm/dd/yyyy)</small> :</label>
    <input type="text" class='textInput' name="edate" maxlength="10" value="<?php if ($rememberFormValues!=0 && isset($_POST['edate'])){echo $original_date;} ?>" >
    <a style='text-decoration:underline; cursor:hand;' onClick="newWindow()">Show calendar</a>
<?php
    for ($ii=1; $ii<=$iimax; $ii++)
    {
      printTimeslot($ii, $iimax);
    }
?>
    <br/><br/><input type="submit" name="submit" value="Create the above slots"><br/>
    </form>

<?php 
  }


  $stmt->close();


  if ($messageCode!=0)
  {
    echo "<div class='generalErr'><b>$message</b></div>\n";
  }
  include "footer.php";
} else {
  echo "You are not authorized to access this page.";
}
include "disconnectDB.php";
?>
