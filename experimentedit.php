<?php
$filename="experimentedit.php";

include "loginCheck.php";
  
include "connectDB.php";
if(isset($_SESSION['permission']) && ($_SESSION['permission']=="admin")){
  $messageCode=0;
  $message="";
      
  //data manipulation
  if (isset($_POST['submit']))
  {
    //This makes sure they did not leave any fields blank
    if (empty($_POST['title']) || empty($_POST['description']) || empty($_POST['hour_credit']) || empty($_POST['location']) || empty($_POST['researcher_email']) || empty($_POST['advisor'])) 
    {
      $message = 'You did not complete all of the required fields';
      $messageCode=1;
    }

    if ($messageCode==0)
    { 
      if (isset($_POST['isopen']) && $_POST['isopen']=="on") $isopen=1; //read value of checkbox
      else $isopen=0;
      $iscompleted=0;

      //now we insert it into the database 
      $stmt = $mysqli->prepare("INSERT INTO experiment VALUES ('0', ?, ?, ?, ?, ?, ?, ?, ?)");
      if(!$stmt) die("Prepare failed");
      $stmt->bind_param('sississs', $_POST['title'], $isopen, $_POST['description'], $_POST['hour_credit'], $iscompleted, $_POST['location'], $_POST['researcher_email'], $_POST['advisor']);
      
      if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      $stmt->close();
      //Print information about success of creation
      $message="Experiment created!";
      $messageCode=2;
    }
  }

  // action=1 ... save change mode
  // action=2 ... remove user mode
  if (isset($_GET['action']))
    switch($_GET['action'])
    {
      /*      case 1:
              {
              //save changes mode
              if (!get_magic_quotes_gpc()) 
              {
              $_POST['title'] = addslashes($_POST['title']);
              $_POST['hour_credit'] = addslashes($_POST['hour_credit']);
              $_POST['location'] = addslashes($_POST['location']);
              $_POST['description'] = addslashes($_POST['description']);
              }
              if (isset($_POST['isopen']) && $_POST['isopen']=="on") $xisopen=1; else {$xisopen=0;} //processing of checkbox
              if (isset($_POST['iscompleted']) && $_POST['iscompleted']=="on") $xiscompleted=1; else {$xiscompleted=0;} //processing of checkbox

              $query = "UPDATE experiment SET title = '".$_POST['title']."', is_open='".$xisopen."', description = '".$_POST['description']."', hour_credit = '".$_POST['hour_credit']."', exp_completed='".$xiscompleted."', location='".$_POST['location']."', researcher_email='".$_POST['researcher_email']."'  WHERE experiment_id = '".$_GET['id']."'";
              $result = mysqli_query($connectionDB, $query) or die(mysqli_error($connectionDB));
              break;
              }
      */
      case 2:
        {
          //remove experiment from DB
          $stmt = $mysqli->prepare("DELETE FROM experiment WHERE experiment_id = ?");
          if(!$stmt) die("Prepare failed");
          $stmt->bind_param('i', $_GET['id']);
          if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
          $stmt->close();

          //find all timeslots, which correspond to an experiment; remove dependencies between request, signsup tables
          $stmt = $mysqli->prepare("SELECT timeslot_id FROM timeslot WHERE experiment_id = ?");
          if(!$stmt) die("Prepare failed");
          $stmt->bind_param('i', $_GET['id']);
          if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
	  $result = $stmt->get_result();
          $stmt->close();
          while($row = $result->fetch_assoc())
          {       
            $timeslotid=$row['timeslot_id'];

            $stmt = $mysqli->prepare("DELETE FROM request WHERE timeslot_id = ?");
            if(!$stmt) die("Prepare failed");
            $stmt->bind_param('i', $timeslotid);
            if (!$stmt->execute()) {
              echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
            }
            $stmt->close();
          
            $stmt = $mysqli->prepare("DELETE FROM signsup WHERE timeslot_id = ?");
            if(!$stmt) die("Prepare failed");
            $stmt->bind_param('i', $timeslotid);
            if (!$stmt->execute()) {
              echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
            }
            $stmt->close();
          }
          //remove corresponding timeslots from DB
          $stmt = $mysqli->prepare("DELETE FROM timeslot WHERE experiment_id = ?");
          if(!$stmt) die("Prepare failed");
          $stmt->bind_param('i', $_GET['id']);
          if (!$stmt->execute()) {
            echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
          }
          $stmt->close();
          break;
        }
    }

  include "header.php"; 
  echo "<div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div>"; 
   
  echo "<font size='3' style='display:block; padding: 25px 10px 10px 0px;'><b>List of all experiments</b></font>";
  echo "<br />";
  //generate table with all the data
  echo "<table>\n";
  echo "  <tbody>\n";
  echo "    <tr>\n";
  echo "     <th>Title</th><th>Open</th><th>Hour/Credit</th><th>Location</th><th>Researcher</th><th>Advisor</th><th colspan=\"2\">Actions</th>\n";
  echo "    </tr>\n";

  $query = "SELECT * FROM experiment ORDER BY experiment_id ASC";
  $result = $mysqli->query($query) or die($mysqli->error);

  $counter6 = 0;
  if(mysqli_num_rows($result)==0) echo "<tr><td colspan='5'>No available experiments</td></tr>";
  while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
  {       
    if(($counter6++)%2==0)  echo "  <tr class='sudy'>\n";
    else                   echo "  <tr class='lichy'>\n";
    //echo "      <td>".$row['experiment_id']."</td>\n";
    echo "      <td>".$row['title']."</td>\n";
    echo "      <td>";
    if ($row['is_open']==1)
    {
      echo "YES";
    }else{
      echo "NO";
    }
    echo "</td>\n";
    echo "      <td>".$row['hour_credit']."</td>\n";
    /* echo "      <td>";
       if ($row['exp_completed']==1)
       {
       echo "YES";
       }else{
       echo "NO";
       }
       echo "</td>\n";*/
    echo "      <td>".$row['location']."</td>\n";
    echo "      <td>".$row['researcher_email']."</td>\n";
	echo "      <td>".$row['advisor']."</td>\n";
    echo "      <td><a href=\"".$filename."\" onclick=\"newWindow(1, ".$row['experiment_id'].")\" ><img class='noborder' src='images/edit_icon.gif' alt='Edit/Show' title='Edit/Show' height='17' width='17'></a>\n"; 
    echo "      <a href='managetimeslots.php?experiment_id=".$row['experiment_id']."' ><img class='noborder' src='images/calendar_icon.gif' alt='Show Timeslots' title='Show Timeslots' height='19' width='27'></a></td>\n"; 
    echo "      <td><a href=\"?id=".$row['experiment_id']."&action=2\"><img onclick='return confirm(\"Are you sure you want to remove this experiment?\");' class='noborder' src='images/delete_icon.gif' alt='Remove' title='Remove' height='15' width='15'></a></td>\n";
    
    echo "    </tr>\n";
  }
  
  echo "  </tbody>\n";
  echo "</table>\n";
  //  echo "</form>\n";

  echo "<br /><br /><br /><p><div align=\"left\">";
  echo "<font size=\"3\"><b>Create a new experiment</b></font>";
  echo "</div></p><br />";

?>

  <!-- Form with text fields etc. -->
       <form action="experimentedit.php" method="post">
      
  <label>Title:</label>
  <input type="text" class="textInput" name="title" value="<?php if ($messageCode!=0){echo $_POST['title'];} ?>">
  <label>Description:</label>
  <textarea name="description" class="textInput"><?php if ($messageCode!=0){echo $_POST['description'];} ?></textarea>
  <label>Hour/credit:</label>
  <input type="text" class="textInput" name="hour_credit" maxlength="20" value="<?php if ($messageCode!=0){echo $_POST['hour_credit'];} ?>">
  <label>Location:</label>
  <input type="text" class="textInput" name="location" value="<?php if ($messageCode!=0){echo $_POST['location'];} ?>">
  <label>Open to sign up?</label>
  <input type="checkbox" name="isopen" checked="<?php if (!isset($_POST['submit']) || (isset($_POST['isopen']) && $_POST['isopen']=="on")) echo "true"; else echo "false" ?>" />
  <label>Researcher:</label>
  <select name="researcher_email">
<?php
  $query = "SELECT email FROM user WHERE role='researcher' OR role='admin' ORDER BY user.email ASC";
  $result = $mysqli->query($query) or die($mysqli->error);

  while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
  {
    echo "        <option";
    if ($messageCode!=0){ if (isset($_POST['researcher_email']) && (strcasecmp($_POST['researcher_email'], $row['email']) == 0)) echo " selected=\"yes\"";}
    echo ">".$row['email']."</option>\n";
  }
?>
  </select>
  <label for="advisor">Advisor:</label>    
  <input type="text" class="textInput" id="advisor" name="advisor" maxlength="3" value="<?php if ($messageCode!=0){echo $_POST['advisor'];} ?>">
  <input type="submit" name="submit" value="Create"><br/>
  </form>

<?php 
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
