<?php
  $filename="statistics.php";
  include "loginCheck.php";
  include "connectDB.php";
  include "library.php";
  if(isset($_SESSION['permission']) && ($_SESSION['permission']=="admin")){
  
  $messageCode=0;
  $message="";
  
  include "header.php";
  echo "<div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div>";  
  echo "<font size='3' style='display:block; padding: 25px 10px 10px 0px;'><b>User Statistics</b></font>";
  
?>    <!-- Form with text fields etc. -->
    <br /><form action="" method="post" name="data">
      <table border="0">
        <tr>
          <th colspan='3'>Date Filter</th>
        </tr>
        <tr>  
          <td>
            Show Information no older than
          </td>
          <td>
            <input type="text" name="edate" maxlength="10" value="<?php if (isset($_POST['edate'])){echo $_POST['edate'];} ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' onClick="newWindow()">Show calendar</a>
          </td>       
          <td><input type="submit" name="submit" value="Apply filter"></td>
        <tr>
      </table>
    </form><br /><br />
<?php
  
  //apply filter
  if (isset($_POST['submit']))
  {
      $startdate=transformDateYearFirst($_POST['edate']);
  }else{
    $startdate="0000-00-00";
  }
 
  $query = "SELECT current_date() as currdate";
  $result = mysqli_query($connectionDB, $query) or die(mysqli_error($connectionDB));
  $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
  $currdate=$row['currdate'];
  
  $query0 = "SELECT user.email, user.name, user.surname, user.role FROM user ORDER BY email ASC";
  $result0 = mysqli_query($connectionDB, $query0) or die(mysqli_error($connectionDB));

  while($row0 = mysqli_fetch_array($result0, MYSQLI_ASSOC))
  {
    echo "<h3>".$row0['email']." (".$row0['name']." ".$row0['surname'].")</h3><br />\n";
    
    $query1 = "SELECT timeslot.timeslot_id, timeslot.edate, timeslot.etime, timeslot.experiment_id FROM signsup LEFT JOIN timeslot ON signsup.timeslot_id=timeslot.timeslot_id WHERE signsup.participant_email='".$row0['email']."' and timeslot.edate>'".$startdate."' and timeslot.edate<'".$currdate."'";
    $result1 = mysqli_query($connectionDB, $query1) or die(mysqli_error($connectionDB));

    echo "<table width=\"100%\">\n";
    echo "<tbody>\n";
    echo "<tr>\n";
    echo "<th>Timeslot ID</th><th>Date</th><th>Time</th><th>Title</th><th>Hour/Credit</th>\n";
    echo "</tr>\n";
    
    $hourcreditsum=0;
    while($row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC))
    {
      echo "<tr>\n";
      $query2 = "SELECT title, hour_credit, experiment_id FROM experiment WHERE experiment.experiment_id='".$row1['experiment_id']."'";
      $result2 = mysqli_query($connectionDB, $query2) or die(mysqli_error($connectionDB));
      $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
      echo "<td>".$row1['timeslot_id']."</td>\n";
      echo "<td>".transformDateYearLast($row1['edate']).", ".dayofweek($row1['edate'])."</td>\n";
      echo "<td>".time24to12($row1['etime'])."</td>\n";
      echo "<td>".$row2['title']."</td>\n";
      echo "<td>".$row2['hour_credit']."</td>\n";
      echo "</tr>";
      $hourcreditsum+=$row2['hour_credit'];
    }

    echo "</tbody>\n";
    echo "</table><br /><br />";
  }

  
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
