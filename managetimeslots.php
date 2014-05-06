<?php
$filename="managetimeslots.php";
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
  $original_date = isset($_REQUEST['edate'])?$_REQUEST['edate']:"";

  // get the experiment value
  $experiment_id = isset($_REQUEST['experiment_id'])?intval($_REQUEST['experiment_id']):0;
  $experiment_name = "";

  if (isset($_REQUEST['reset']) )
  {
    header("Location: ".$filename);
  }

  //This code runs if the form has been submitted
  if (isset($_REQUEST['submit']) )
  {    
    //to remember correct number of timeslots, when not all required fields filled in correctly
    if (isset($_REQUEST['id']) )
    {
      $iimax=$_REQUEST['id'];
    }else{
      $iimax=1;
    }    
    //This makes sure they did not leave any fields blank
    if (empty($_REQUEST['experiment_id']) || empty($_REQUEST['edate']) ) 
    {
      $message = 'You did not complete all of the required fields';
      $messageCode=1;
    }    

    if ($messageCode==0)
    {     
      $original_date = $_REQUEST['edate'];
      $_REQUEST['edate'] = transformDateYearFirst($_REQUEST['edate']);
      for ($ii=1; $ii<=$iimax;$ii++)
      {
        $hour="hour".$ii;
        $minute="mimute".$ii;
        $daypart="daypart".$ii;
        $capacity="capacity".$ii;

        $etime1=time12to24($_REQUEST[$hour], $_REQUEST[$minute], $_REQUEST[$daypart]);

        //now we insert it into the database 
        if (!($stmt = $mysqli->prepare("INSERT INTO timeslot VALUES ('0', ?, ?, ?, '0', ?)"))) {
          echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        if(!($stmt->bind_param("issi", $_REQUEST['experiment_id'], $_REQUEST['edate'], $etime1, $_REQUEST[$capacity]))){
          echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
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
    $rememberFormValues=1;
    $iimax=$_REQUEST['id']+1;
  }
  //remove a slot
  if (isset($_REQUEST['removeslot']) )
  {
    $rememberFormValues=1;
    $iimax=$_REQUEST['id']-1;
  }


  include "header.php";
  echo "<div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div>";    
  echo "<font size='3' style='display:block; padding: 25px 10px 10px 0px;'><b>Timeslot Management</b></font>";

?>
  <form name="experiment" action=<?php echo $filename?> method="get">
  <label>Select Experiment:</label>
  <select name="experiment_id">
<?php
  //find all experiments
  $query = "SELECT experiment_id, title FROM experiment ORDER BY experiment.title ASC";
  $result = $mysqli->query($query) or die($mysqli->error);
    
  while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
  {
    echo "<option value=\"".$row['experiment_id']."\"";
    if (isset($_REQUEST['experiment_id']) && $experiment_id==$row['experiment_id']){
      echo " selected=\"yes\" ";
      $experiment_name=$row['title'];
    }
    echo " >".$row['title']."</option>\n";
  }
?>
  </select>
  <input type="submit" value ="Select" />
  </form>
<?php


  if(isset($_REQUEST['experiment_id'])){


    echo "<br /><hr /><br /><h3>$experiment_name</h3> <br /><br /><div align=\"left\">";
    echo "<font size=\"3\"><b>Timeslots</b></font><br /><br />";
    echo "</div>";

    //generate table with all the data
    echo "<table>\n";
    echo "  <tbody>\n";
    echo "    <tr>\n";
    echo "      <th>Date</th><th>Time</th><th>Total Seats</th><th>Available</th><th>Participants</th><th>Remove</th>\n";
    echo "    </tr>\n";

    if (!($stmt = $mysqli->prepare("SELECT timeslot.*, experiment.title, experiment.researcher_email, 
				( timeslot.capacity_total - 
				  (SELECT count(*) from signsup 
				   WHERE signsup.timeslot_id=timeslot.timeslot_id ) 
				) as available 
				FROM experiment 
				inner JOIN timeslot ON experiment.experiment_id=timeslot.experiment_id 
				WHERE experiment.experiment_id=? 
				ORDER BY timeslot.edate ASC, timeslot.etime ASC"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    $stmt->bind_param("i", $experiment_id);
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    $result = $stmt->get_result();
    $stmt->close();
    $counter=0;
    if(mysqli_num_rows($result)==0) echo "<tr><td colspan='5'>No available timeslots</td></tr>";
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
      if(($counter++)%2==0)  echo "  <tr class='sudy'>\n";
      else                   echo "  <tr class='lichy'>\n";    
      //echo "    <td>".$row['title']."</td>\n";
      echo "    <td>".transformDateYearLast($row['edate']).", ".dayofweek($row['edate'])."</td>\n";
      echo "    <td>".time24to12($row['etime'])."</td>\n";
      echo "    <td>".$row['capacity_total']."</td>\n";
      echo "    <td>".$row['available']."</td>\n";
      echo "    <td><a href=\"?experiment_id=$experiment_id&id=".$row['timeslot_id']."&action=1#users\"><img class='noborder' src='images/users_icon.gif' alt='Show Participants' title='Show Participants' height='25' width='31'></a></td>\n";
      echo "    <td><a onclick=\"return confirm('Are you sure you want to delete this timeslot?');\" href=\"?experiment_id=$experiment_id&id=".$row['timeslot_id']."&action=0\"><img class='noborder' src='images/delete_icon.gif' alt='Remove Timeslot' title='Remove Timeslot' height='15' width='15'></a></td>\n";
      echo "  </tr>\n";
    }
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
            echo "      <th>Participant Email</th><th>Remove</th>\n";
            echo "    </tr>\n"; 

            $counter2 = 0;  
            if(mysqli_num_rows($result)==0) echo "<tr><td colspan='2'>No participants</td></tr>";

            while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
            {
              if(($counter2++)%2==0)  echo "  <tr class='sudy'>\n";
              else                   echo "  <tr class='lichy'>\n";
              echo "      <td>".$row['participant_email']."</td><td><a href=\"?experiment_id=$experiment_id&id=".$row['sign_up_id']."&action=2#users\"><img onclick=\"return confirm('Are you sure you want to remove this participant?');\" class='noborder' src='images/delete_icon.gif' alt='Remove Participant' title='Remove Participant' height='15' width='15'></a></td>\n";
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

    echo "<br /><br /><br /><p><div align=\"left\">";
    echo "<font id='add' size=\"3\"><b>Create a new timeslot(s)</b></font>";
    echo "</div></p><br />";
?>
    <!-- Form with text fields etc. -->
    <form name="data" action=<?php echo '"'.$filename; echo '?experiment_id='.$experiment_id.'#add"';?> method="post">
    <label>Date:<small>(mm/dd/yyyy)</small></label>
    <input type="text" class='textInput' name="edate" maxlength="10" value=<?php if ($rememberFormValues!=0 && isset($_REQUEST['edate'])){echo $original_date;} ?>>
    
    <a style='text-decoration:underline; cursor:hand;' onClick="newWindow()">Show calendar</a>
<?php
    for ($ii=1; $ii<=$iimax; $ii++)
    {
      printTimeslot($ii, $iimax);
    }
?> 
    <br/><br/><input type="submit" name="submit" value="Create the above slots"><br/>
    </form>
    <!--
    <form name="data2" action=<?php echo '"'.$filename; echo '?experiment_id='.$experiment_id.'#add@"';?> method="POST">
    </form>-->

<?php 

  } // if not set experiment id cycle end
  else echo '<br /><br /><br /><br /><br /><br /><br />';



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
