<?php

//original design 2008, Karel Simek
//changes July 2010, Jiri Matousek, jiri.matousek@mensa.cz
require("require.php");
$filename = "showall.php";
include "loginCheck.php";
include "connectDB.php";
include "library.php";

$messageCode = 0;
$message = "";
$editmode = 0;
include "header.php";

//get currdate
$query_date = "SELECT current_date() as currdate";
$result_date = $mysqli->query($query_date) or die($mysqli->error);
$row_date = mysqli_fetch_array($result_date, MYSQLI_ASSOC);
$currdate = $row_date['currdate'];

//get currtime
$query_time = "SELECT current_time() as currtime";
$result_time = $mysqli->query($query_time) or die($mysqli->error);
$row_time = mysqli_fetch_array($result_time, MYSQLI_ASSOC);
$currtime = $row_time['currtime'];


if ((isset($_SESSION['permission']) && $_SESSION['permission'] == "admin") or
    (isset($_SESSION['permission']) && $_SESSION['permission'] == "researcher")
    ) {

  echo "<div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div><br />";
}

if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    //show all timeslots for selected experiment
    case 1: {

				
      $stmt = $mysqli->prepare("SELECT timeslot.*, experiment.title, experiment.location, experiment.hour_credit, experiment.researcher_email FROM timeslot LEFT JOIN experiment ON timeslot.experiment_id=experiment.experiment_id WHERE timeslot.timeslot_id=?");
      if(!$stmt) die("Prepare failed");
      $stmt->bind_param('i', $_GET['tid']);
      if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      $result2=$stmt->get_result();
      $stmt->close();
      $row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
      //check to see if already signed up for experiment //stangles 12/14/11
      $stmt = $mysqli->prepare("SELECT sign_up_id FROM signsup LEFT JOIN timeslot ON timeslot.timeslot_id=signsup.timeslot_id WHERE timeslot.experiment_id=? AND participant_email=?");
      if(!$stmt) die("Prepare failed");
      $stmt->bind_param('is', $_GET['eid'], $_SESSION['email']);
      if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      $result0=$stmt->get_result();
      $stmt->close();
      $num0 = mysqli_num_rows($result0);
      if($num0 == 0){
        //insert information into request table
        $stmt = $mysqli->prepare("INSERT INTO signsup (timeslot_id,participant_email) VALUES ( ?, ?)");
        if(!$stmt) die("Prepare failed");
        $stmt->bind_param('is', $_GET['tid'], $_SESSION['email']);
        if (!$stmt->execute()) {
          echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        $result6=$stmt->get_result();
        $stmt->close();
        if ($result6) {
          //construct an email body
          $email = $_SESSION['email'];
          $subject = "FREUD Online:  your timeslot signup";

          ///
          $emailmessage = "You have requested the following slot:<br><br>\n";

          $emailmessage = $emailmessage . "<table border='0'><tr><td>Title:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t" . $row2['title'] . "</td></tr>\n";
          $emailmessage = $emailmessage . "<tr><td>Location:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t" . $row2['location'] . "</td></tr>\n";
          $emailmessage = $emailmessage . "<tr><td>Hour/credit:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t" . $row2['hour_credit'] . "</td></tr>\n";

          $emailmessage = $emailmessage . "<tr><td>Date:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t" . transformDateYearLast($row2['edate']) . ", " . dayofweek($row2['edate']) . "</td></tr>\n";
          $emailmessage = $emailmessage . "<tr><td>Time:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t" . time24to12($row2['etime']) . "</td></tr></table>\n";

          $emailmessage = $emailmessage . "<br>If you need to cancel, please contact the researcher at <a href='mailto: " . $row2['researcher_email'] . "'>" . $row2['researcher_email'] . "</a> or reply to this email.\n";

          $emailmessage = $emailmessage . "<br>Thank you for your participation!<br>\n";


          $emailmessage = $emailmessage . "<br><br><small>generated by UCITS</small><br>";

          $headers = 'From: ' . $server_email_address . "\r\n";  
          'Reply-To: ' . $row2['researcher_email'] . "\r\n";
          $headers.= "Content-Type: text/html; charset=ISO-8859-1 ";
          $headers .= "MIME-Version: 1.0 ";
          //send an email
          mail($email, $subject, $emailmessage, $headers);
          /////
          //Now we let them know if their registration was successful
          $message = "Signup email sent!";
          $messageCode = 5;
        } else { /* error inserting */
          $message = "Oops, you have already signed up!";
          $messageCode = 10;
        }
      } else {
        $message = "Oops, you have already signed up!";
        $messageCode = 11;
      }
      break;
    }
    case 0: {
      //extract title from experiment with eid
      $stmt = $mysqli->prepare("SELECT experiment_id, title FROM experiment WHERE experiment_id=?");
      if(!$stmt) die("Prepare failed");
      $stmt->bind_param('i', $_GET['eid']);
      if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      $result=$stmt->get_result();
      $stmt->close();
      $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

      echo "<br /><br /><br /><p><div align=\"left\">";
      echo "<font class='showIcon' id=\"slots\" size=\"3\"><b><span class='manageallt'>" . $row['title'] . " Timeslots</span></b></font><br />";
      echo "</div></p><br />";

      echo "<table>\n";
      echo "  <tbody>\n";
      echo "  <tr><th>Date</th><th>Time</th><th>Seats available</th><th>Actions</th><th>Status</th></tr>\n";


      //am I already signed in any timeslot for this
      //experiment?? changed July 27 2010
      $stmt = $mysqli->prepare("SELECT sign_up_id FROM signsup LEFT JOIN timeslot ON
timeslot.timeslot_id=signsup.timeslot_id WHERE
timeslot.experiment_id=? AND participant_email=?");
      if(!$stmt) die("Prepare failed");
      $stmt->bind_param('is', $_GET['eid'], $_SESSION['email']);
      if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      $result0=$stmt->get_result();
      $stmt->close();
      $num0 = mysqli_num_rows($result0);



      //show all timeslots for given experiment, only future timeslots, hide expired timeslots
      $stmt = $mysqli->prepare("SELECT timeslot.* 
					FROM timeslot LEFT JOIN experiment 
					ON timeslot.experiment_id=experiment.experiment_id 
					WHERE timeslot.experiment_id=? 
					AND (edate>? 
							OR (edate=? AND etime>=?)) 
					ORDER BY timeslot.edate ASC, timeslot.etime ASC");
      if(!$stmt) die("Prepare failed");
      $stmt->bind_param('issi', $_GET['eid'], $currdate, $currdate, $currtime);
      if (!$stmt->execute()) {
        echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
      }
      $result=$stmt->get_result();
      $result = $result->fetch_all(MYSQLI_ASSOC);
      $stmt->close();
      $counter4 = 0;
                
      foreach ($result as $row) {
        $stmt = $mysqli->prepare("SELECT * FROM signsup LEFT JOIN timeslot ON timeslot.timeslot_id=signsup.timeslot_id WHERE timeslot.timeslot_id=?");
        if(!$stmt) die("Prepare failed");
        $stmt->bind_param('i', $row['timeslot_id']);
        $stmt->execute();
        $result2 = $stmt->get_result();
        $stmt->close();
        $num2 = mysqli_num_rows($result2);
        $capacity_left = ($row['capacity_total'] - $num2);
        //echo $capacity_left;
        //am I already signed in this timeslot??
        $stmt = $mysqli->prepare("SELECT * FROM signsup WHERE participant_email=? and timeslot_id=?");
        if(!$stmt) die("Prepare failed");
        $stmt->bind_param('si', $_SESSION['email'], $row['timeslot_id']);
        $stmt->execute();
        $result3 = $stmt->get_result();
        $stmt->close();
        $num3 = mysqli_num_rows($result3);
                  
        if (($counter4++) % 2 == 1)
          echo "    <tr class='sudy'>\n";
        else
          echo "    <tr class='lichy'>\n";
        echo "    <td>" . transformDateYearLast($row['edate']) . ", " . dayofweek($row['edate']) . "</td>\n";
        echo "    <td>" . time24to12($row['etime']) . "</td>\n";
        echo "    <td>" . $capacity_left . "</td>\n";
        echo "    <td>";
        if ($num3 > 0) {
          echo "*";
        } else if ($capacity_left > 0 and $num0 == 0) {
          echo "<a onclick=\"return confirm('Sign up for this timeslot? (you will get an email)');\" href=\"?tid=" . $row['timeslot_id'] . "&action=1\">Signup</a>";
        } else {
          if ($num0 != 0
              )echo "Other slot taken";
          else
            echo "*";
        }
        echo "</td>\n";

        echo "    <td>";
        if ($num3 > 0) {
          echo "<span style='color:green;font-weight:bold'>Signed up</span>";
        } else if ($capacity_left > 0 and $num0==0) {
          echo "Available";
        } else if($num0!=0){
          echo "You already signed up for this experiment!";            
        }else {
          echo "<span style='color:red;font-weight:bold'>Full</span>";
        }
        echo "</td>\n";
        echo "  </tr>\n";
      }
      echo "  </tbody>\n";
      echo "</table>\n";
      break;
    }
  }
}else{



  echo "<font class='showIcon' size=\"3\"><b><span class='managee'>Available Experiments</span></b></font><br />";
  echo "<br />";

  //generate table with all the data
  echo "<table class='mytable'>\n";
  echo "  <tbody>\n";
  echo "    <tr>\n";
  echo "      <th>Info</th><th>Description</th><th>Timeslots</th>\n";
  echo "    </tr>\n";
  $stmt = $mysqli->prepare("SELECT * , (SELECT COUNT(*) FROM timeslot t2
		INNER JOIN signsup 
		ON signsup.timeslot_id=t2.timeslot_id 
		AND participant_email=?) as kolik
		FROM experiment WHERE 
		( (select sum(capacity_total) 
		   FROM timeslot t 
		   where experiment.experiment_id = t.experiment_id
		   AND (edate>current_date() OR (edate=current_date() AND etime>=current_time()))
		  ) 
		  - 
		  (SELECT count(*) from signsup r INNER join timeslot t on t.timeslot_id = r.timeslot_id 
		   WHERE experiment.experiment_id=t.experiment_id
		   AND (edate>current_date() OR (edate=current_date() AND etime>=current_time()))
		  )
		) > 0        		  
		AND is_open=1 
		AND exp_completed=0
		ORDER BY title");
  if(!$stmt) die("Prepare failed");
  $stmt->bind_param('s', $_SESSION['email']);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();
  if (mysqli_num_rows($result) == 0)
    echo "<tr><td colspan='3'>No available experiments</td></tr>";
  $counter = 0;
  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $counter++;
    if ($row['is_open'] == 1 && $row['exp_completed'] == 0) {
      //The following sees if you are registered for the specific experiment
      $stmt = $mysqli->prepare("SELECT * FROM timeslot WHERE experiment_id =?");
      if(!$stmt) die("Prepare failed");
      $stmt->bind_param('i', $row['experiment_id']);
      $stmt->execute();
      $theresult = $stmt->get_result();
      $stmt->close();
      $thecount = 0;
      while ($therow = mysqli_fetch_array($theresult, MYSQLI_ASSOC)) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM signsup WHERE timeslot_id = ? AND participant_email = ?");
        if(!$stmt) die("Prepare failed");
        $stmt->bind_param('is', $therow['timeslot_id'], $_SESSION['email']);
        $stmt->execute();
        $myresult = $stmt->get_result();
        $stmt->close();
        while($array = mysqli_fetch_row($myresult)){
          if( $array[0] == 1)
            $thecount = 1;
        }
      }		
      // $thecount is now used as 1 if registered 0 otherwise.

      if ($thecount != 0)
        echo "<tr class='selectedExperiment'>";
      elseif ($counter % 2 == 0)
          echo "<tr class='sudy'>";
      else
        echo "<tr class='lichy'>";

      echo "      <td style='vertical-align:top'>";
      echo "<span class='ETitle'>" . $row['title'] . "</span>\n";
      echo"
				<table class='innerTable'>
				<tr>
				<td><img src='images/check.png' alt='Credit/$' title='Credit/$' height='32' width='32'></td>
				<td>" . $row['hour_credit'] . "</td>
				</tr>
				<tr>
				<td><img src='images/smallhome.gif' alt='Location' title='Location' height='24' width='29'></td>
				<td>" . $row['location'] . "</td>
				</tr>
				<tr>
				<td><img src='images/user.gif' alt='Researcher' title='Researcher' height='22' width='22'></td>
				<td>" . $row['researcher_email'] . "</td>
				</tr>
				</table>
				</td>
				";
      echo "      <td><span class='EDescription'>\n";
      echo $row['description'] . "\n";
      echo "      </span>";
      echo "</td>\n";
      echo '<td>';
      if ($thecount != 0)
        echo "<img src='images/tick.gif' alt='Signed up!' title='Signed up!' height='36' width='38'>";

      echo "      <a href=\"?action=0&eid=" . $row['experiment_id'] . "#slots\"><img class='noborder' src='images/calendar_icon.gif' alt='Show Timeslots' title='Show Timeslots' height='23' width='27'></a>\n";
      echo "    </td></tr>\n";
    }
  }




  echo "  </tbody>\n";
  echo "</table>\n";

  echo "<br /><font size=\"1\">($) indicates that cash payment for participation is available.</font><br />";
  echo "<font size=\"1\">Only experiments you have not signed up and have slots available are shown</font>";


}


include "disconnectDB.php";

if ($messageCode != 0) {
  echo "<div class='generalErr'><b>$message</b></div>\n";
}
include "footer.php";
?>
