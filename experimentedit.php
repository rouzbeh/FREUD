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
      // add slashes if needed
      if (!get_magic_quotes_gpc()) 
      {
        $_POST['title'] = addslashes($_POST['title']);
        $_POST['description'] = addslashes($_POST['description']);
        $_POST['hour_credit'] = addslashes($_POST['hour_credit']);
        $_POST['location'] = addslashes($_POST['location']);
        $_POST['researcher_email'] = addslashes($_POST['researcher_email']);
		$_POST['advisor'] = addslashes($_POST['advisor']);
      }
  
      if (isset($_POST['isopen']) && $_POST['isopen']=="on") $isopen=1; //read value of checkbox
      else $isopen=0;
      $iscompleted=0;

      //now we insert it into the database 
      $query = "INSERT INTO experiment VALUES ('0', '".$_POST['title']."', '".$isopen."', '".$_POST['description']."', '".$_POST['hour_credit']."', '".$iscompleted."','".$_POST['location']."', '".$_POST['researcher_email']."', '".$_POST['advisor']."')";
      $result = mysqli_query($connectionDB, $query) or die(mysqli_error());
    
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
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());
        break;
      }
*/
      case 2:
      {
        //remove experiment from DB
        $query = "DELETE FROM experiment WHERE experiment_id = '".$_GET['id']."'";
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());

        //find all timeslots, which correspond to an experiment; remove dependencies between request, signsup tables
        $query = "SELECT timeslot_id FROM timeslot WHERE experiment_id = '".$_GET['id']."'";
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());
        while($row = mysqli_fetch_array($result, MYSQL_ASSOC))
        {       
          $timeslotid=$row['timeslot_id'];

          $query0 = "DELETE FROM request WHERE timeslot_id = '".$timeslotid."'";
          $result0 = mysqli_query($connectionDB, $query0) or die(mysqli_error());

          $query1 = "DELETE FROM signsup WHERE timeslot_id = '".$timeslotid."'";
          $result1 = mysqli_query($connectionDB, $query1) or die(mysqli_error());
        }

        //remove corresponding timeslots from DB
        $query = "DELETE FROM timeslot WHERE experiment_id = '".$_GET['id']."'";
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());

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
  $result = mysqli_query($connectionDB, $query) or die(mysqli_error());

  $counter6 = 0;
  if(mysqli_num_rows($result)==0) echo "<tr><td colspan='5'>No available experiments</td></tr>";
  while($row = mysqli_fetch_array($result, MYSQL_ASSOC))
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
<table style='width:auto'>
  <tr><th colspan='2'>New Experiment</th></tr>
  <tr>    
    <td>Title:</td><td>
      <input type="text" class='textInput' name="title" maxlength="50" value="<?php if ($messageCode!=0){echo $_POST['title'];} ?>">
    </td>
  </tr>
  <tr>
    <td>Description:</td><td>
      <textarea name="description" class='textInput'  rows="15" cols="50"><?php if ($messageCode!=0){echo $_POST['description'];} ?></textarea>
    </td>
  </tr>
  <tr>
    <td>Hour/credit:</td><td>
      <input type="text" class='textInput'  name="hour_credit" maxlength="20" value="<?php if ($messageCode!=0){echo $_POST['hour_credit'];} ?>">
    </td>
  </tr>
  <tr>
    <td>Location:</td><td>
      <input type="text" class='textInput'  name="location" maxlength="50" value="<?php if ($messageCode!=0){echo $_POST['location'];} ?>">
    </td>
  </tr>
  <tr>
    <td>Open to sign up?</td><td>    
      <input type="checkbox" class='textInput'  name="isopen" <?php if (!isset($_POST['submit']) || (isset($_POST['isopen']) && $_POST['isopen']=="on")) echo "checked"; ?>>
    </td>
  </tr>
  <tr>
    <td>Researcher:</td><td>
      <select name="researcher_email">
<?
        $query = "SELECT email FROM user WHERE role='researcher' OR role='admin' ORDER BY `user`.`email`ASC";
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());

        while($row = mysqli_fetch_array($result, MYSQL_ASSOC))
        {
          echo "        <option";
          if ($messageCode!=0){ if (isset($_POST['researcher_email']) && (strcasecmp($_POST['researcher_email'], $row['email']) == 0)) echo " selected=\"yes\"";}
          echo ">".$row['email']."</option>\n";
        }
?>
      </select>
    </td>
  </tr>
    <tr>
    <td>Advisor:</td><td>    
      <input type="text" class='textInput'  name="advisor" maxlength="3" value="<?php if ($messageCode!=0){echo $_POST['advisor'];} ?>">
    </td>
  </tr>
  <tr>
    <td colspan=2>
      <input type="submit" name="submit" value="Create">
      <input type="reset" name="reset" value="Reset">
    </td>
  </tr> 
</table>
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
