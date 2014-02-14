<?php
  $filename="timeslotedit.php";
	require("require.php");
  include "loginCheck.php";
  
  include "connectDB.php";

  include "library.php";  
  if(isset($_SESSION['permission']) && ($_SESSION['permission']=="admin" || $_SESSION['permission']=="researcher")){
  $messageCode=0;
  $message="";
  
  $rememberFormValues=0;
  $editmode=0;
  $original_date = $_POST['edate'];
  
  // sends email to users while the timeslot is deleted
  function sendMailToParticipants($id){
        $query = "SELECT participant_email FROM signsup WHERE timeslot_id='$id'";
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());        
        $emails = Array();
        while($row = mysqli_fetch_array($result, MYSQL_ASSOC)){
             $emails[] = $row['participant_email'];   
        }
        
       
        $query2 = "SELECT title, hour_credit, location, researcher_email, etime,edate  FROM experiment INNER JOIN timeslot ON experiment.experiment_id=timeslot.experiment_id WHERE timeslot_id='".$id."'";
        //echo $query2;
        $result2 = mysqli_query($connectionDB, $query2) or die(mysqli_error());
        $row2 = mysqli_fetch_array($result2, MYSQL_ASSOC);
    
        //construct an email body
        $email= implode($emails,', ');
        $subject="FREUD Online:  signup canceled!";
        
        ///        
        $emailmessage="The following slot you have signed up for has been deleted:<br><br>\n";
        
        $emailmessage=$emailmessage."<table border='0'><tr><td>Title:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".$row2['title']."</td></tr>\n";
        $emailmessage=$emailmessage."<tr><td>Location:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".$row2['location']."</td></tr>\n";
        $emailmessage=$emailmessage."<tr><td>Hour/credit:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".$row2['hour_credit']."</td></tr>\n";
  
        $emailmessage=$emailmessage."<tr><td>Date:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".transformDateYearLast($row2['edate']).", ".dayofweek($row2['edate'])."</td></tr>\n";
        $emailmessage=$emailmessage."<tr><td>Time:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".time24to12($row2['etime'])."</td></tr></table>\n";
                
        $emailmessage=$emailmessage."<br>Thank you for your participation!<br>\n";
        
  
        $emailmessage=$emailmessage."<br><br><small>generated by UCITS</small><br>";    
  	  
  	    $headers = 'From: ' $server_email_address . "\r\n";      
        $headers .= "Bcc: {$email}" . "\r\n";                   
  	    $headers .= 'Reply-To: '. $row2['researcher_email'] . "\r\n";
  	    $headers .= "Content-Type: text/html; charset=ISO-8859-1 ";
        $headers .= "MIME-Version: 1.0 "; 
        //send an email
        mail($server_email_address,$subject,$emailmessage, $headers);
        //echo $subject.'<br /><br />'.$emailmessage.'<br /><br />'.$headers;
  
  }
  
  
  // send email to user with canceled signup
  function sendMailToUser($id){ // signup id
        $query = "SELECT participant_email FROM signsup WHERE sign_up_id='$id'";
        //echo $query;
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());        
        $emails = Array();
        $row = mysqli_fetch_array($result, MYSQL_ASSOC);             
        $email = $row['participant_email'];   
        //echo $email;
        
        
        $query2 = " SELECT title, hour_credit, location, researcher_email, etime,edate 
                    FROM experiment 
                    INNER JOIN timeslot 
                    ON experiment.experiment_id=timeslot.experiment_id 
                    INNER JOIN signsup 
                    ON signsup.timeslot_id = timeslot.timeslot_id
                    WHERE signsup.sign_up_id='".$id."'";
        //echo $query2;
        $result2 = mysqli_query($connectionDB, $query2) or die(mysqli_error());
        $row2 = mysqli_fetch_array($result2, MYSQL_ASSOC);
        
        //construct an email body
        //$email=$_SESSION['email'];
        $subject="FREUD Online:  signup canceled!";
        
        ///        
        $emailmessage="You have been removed from the following slot:<br><br>\n";
        
        $emailmessage=$emailmessage."<table border='0'><tr><td>Title:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".$row2['title']."</td></tr>\n";
        $emailmessage=$emailmessage."<tr><td>Location:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".$row2['location']."</td></tr>\n";
        $emailmessage=$emailmessage."<tr><td>Hour/credit:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".$row2['hour_credit']."</td></tr>\n";
  
        $emailmessage=$emailmessage."<tr><td>Date:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".transformDateYearLast($row2['edate']).", ".dayofweek($row2['edate'])."</td></tr>\n";
        $emailmessage=$emailmessage."<tr><td>Time:</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\t".time24to12($row2['etime'])."</td></tr></table>\n";
        
        $emailmessage=$emailmessage."<br>If you don't expect this email, please contact the researcher at <a href='mailto: ".$row2['researcher_email']."'>".$row2['researcher_email']."</a> or reply to this email.\n";
       
        $emailmessage=$emailmessage."<br>Thank you for your participation!<br>\n";
        
  
        $emailmessage=$emailmessage."<br><br><small>generated by UCITS</small><br>";    
  	  
  	    $headers = 'From: ' $server_email_address . "\r\n";  
  	    $headers = 'Reply-To: '. $row2['researcher_email'] . "\r\n";
  	    $headers.= "Content-Type: text/html; charset=ISO-8859-1 ";
        $headers .= "MIME-Version: 1.0 "; 
        //send an email
        //echo $email.'<br /><br />'.$subject.'<br /><br />'.$emailmessage.'<br /><br />'.$headers;
        if($email!='') mail($email,$subject,$emailmessage, $headers);
        
  
  
  }
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
      // add slashes if needed
      if (!get_magic_quotes_gpc()) 
      {
        $_POST['experiment_id'] = addslashes($_POST['experiment_id']);
        $_POST['edate'] = addslashes($_POST['edate']);
      }
      
            
      $_POST['edate'] = transformDateYearFirst($_POST['edate']);
      //echo $_REQUEST['edate'];
      //save all timeslots to timeslot table
//      $linesToSave=1;
//      if (isset($_GET['action']) && isset($_GET['id']) && $_GET['action']==3)
//      {
//        $linesToSave=$_GET['id'];
//      }
//      if ($linesToSave<=0){$linesToSave=1;}
      
//      for ($ii=1; $ii<=$linesToSave;$ii++)
      for ($ii=1; $ii<=$iimax;$ii++)
      {
        $hour="hour".$ii;
        $minute="mimute".$ii;
        $daypart="daypart".$ii;
        $capacity="capacity".$ii;
        
/*        $etime1=$_POST[$hour];
        if ($_POST[$daypart]=="pm") $etime1+=12;
        if ($etime1==24) $etime1=0;
        $etime1=$etime1.":".$_POST[$minute].":00";
*/
        $etime1=time12to24($_POST[$hour], $_POST[$minute], $_POST[$daypart]);

        //now we insert it into the database 
        $query = "INSERT INTO timeslot VALUES ('0', '".$_POST['experiment_id']."', '".$_POST['edate']."', '".$etime1."', '0','".$_POST[$capacity]."' )";
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());              
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
        
        $query = "DELETE FROM timeslot WHERE timeslot_id = '".$_GET['id']."'";
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());
        header("Location: ".$filename."?experiment_id=".$_REQUEST['experiment_id']);
        break;
      }
      // delete already confirmed signup
      case 2:
      {
        
        sendMailToUser($_GET['id']); // notify user about it
        
        $query1 = "DELETE FROM signsup WHERE sign_up_id='".$_GET['id']."'";
        $result1 = mysqli_query($connectionDB, $query1) or die(mysqli_error());
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

  
  include "header.php";
     
  
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

  $query = "SELECT timeslot.*, experiment.title, experiment.researcher_email, 
            ( timeslot.capacity_total - 
             (SELECT count(*) from signsup 
              WHERE signsup.timeslot_id=timeslot.timeslot_id ) 
            	) as available 
            FROM experiment 
            inner JOIN timeslot ON experiment.experiment_id=timeslot.experiment_id 
            WHERE $que experiment.researcher_email='".$_SESSION['email']."' 
            ORDER BY timeslot.edate ASC, timeslot.etime ASC"; 
            //DATE_SUB(CURDATE(),INTERVAL 14 DAY) <= timeslot.edate AND          
  //echo $query;
  $result = mysqli_query($connectionDB, $query) or die(mysqli_error());
  $counter=0;
  if(mysqli_num_rows($result)==0) echo "<tr><td colspan='7'>No timeslots available</td></tr>";
  while($row = mysqli_fetch_array($result, MYSQL_ASSOC))
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
        $query = "SELECT * FROM signsup LEFT JOIN timeslot ON signsup.timeslot_id=timeslot.timeslot_id WHERE signsup.timeslot_id = '".$_GET['id']."'";
        $result = mysqli_query($connectionDB, $query) or die(mysqli_error());

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
        while($row = mysqli_fetch_array($result, MYSQL_ASSOC))
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

  function printTimeslot($id, $idmax)
  {
    global $rememberFormValues;
    
    $hour="hour".$id;
    $minute="mimute".$id;
    $daypart="daypart".$id;
    $capacity="capacity".$id;
  
    echo "<tr>\n";
    echo "  <td>Timeslot ".$id.":</td><td>";
    echo "    <select name=\"".$hour."\">\n";

    for ($ii=1; $ii<=12;$ii++)
    {
	  if ($ii<10) $ii="0".$ii;
      echo "        <option value=\"".$ii."\"";
      if ($rememberFormValues!=0 && isset($_POST[$hour]) && $_POST[$hour]==$ii){echo " selected=\"yes\" ";}
      echo ">".$ii."</option>\n";
    }

    echo "    </select>";
    echo "    <select name=\"".$minute."\">\n";

    for ($ii=0; $ii<60;$ii+=5)
    {
	  if ($ii<10) $ii="0".$ii;
      echo "        <option value=\"".$ii."\"";
      if ($rememberFormValues!=0 && isset($_POST[$minute]) && $_POST[$minute]==$ii){echo " selected=\"yes\" ";}
      echo ">".$ii."</option>\n";
    }

    echo "   </select>\n";
    echo "    <select name=\"".$daypart."\">\n";
    echo "      <option value=\"am\""; if ($rememberFormValues!=0 && isset($_POST[$daypart]) && $_POST[$daypart]=="am"){echo " selected=\"yes\" ";} echo ">am</option>\n";
    echo "      <option value=\"pm\""; if ($rememberFormValues!=0 && isset($_POST[$daypart]) && $_POST[$daypart]=="pm"){echo " selected=\"yes\" ";} echo ">pm</option>\n";
    echo "    </select>\n";
    echo "  </td>\n";

    echo "  <td>Capacity:</td>\n";
    echo "  <td>\n";
    echo "    <select name=\"".$capacity."\">\n";
    for ($ii=1; $ii<=12;$ii++)
    {
      echo "        <option value=\"".$ii."\"";
      if ($rememberFormValues!=0 && isset($_POST[$capacity]) && $_POST[$capacity]==$ii){echo " selected=\"yes\" ";}
      echo ">".$ii."</option>\n";
    }
    echo "    </select>\n";
    echo "  </td>\n";

    echo "  <td>\n";
    if ($id==$idmax)
    {
      echo "    <input type=\"submit\" name=\"addslot\" value =\"Make more slots\">\n";
      echo "    <input type=\"submit\" name=\"removeslot\" value =\"Remove this slot\">\n";
      echo "    <input type=\"hidden\" name=\"id\" value =\"".$id."\">\n";

    }else{
      echo " ";
    }
    echo "  </td>\n";
    
    echo "</tr>\n";

  }
  //echo $_POST['edate'];
  echo "<br /><br /><br /><p><div align=\"left\">";
  echo "<font size=\"3\"><b>Create a new timeslot(s)</b></font>";
  echo "</div></p><br />";
  echo '<!-- Form with text fields etc. -->';
  //find all experiments lead by user with currently logged researcher
  $query = "SELECT experiment_id, title FROM user inner JOIN experiment ON user.email=experiment.researcher_email WHERE email='".$_SESSION['email']."'";
  //echo $query;
  $result = mysqli_query($connectionDB, $query) or die(mysqli_error());
  $num =  mysqli_num_rows($result);
  if($num==0) echo 'You have no experiment assigned';
  else {
  
?>

<form name="data" action="<?echo $filename;?>" method="post">
<table style="width:auto">  
  <tr><th colspan='5'>New Timeslot</th></tr>
  <tr>
    <td>Experiment:</td><td>
      <select name="experiment_id">
<?
        
        while($row = mysqli_fetch_array($result, MYSQL_ASSOC))
        {
          echo "        <option value=\"".$row['experiment_id']."\"";
          if ($rememberFormValues!=0 && isset($_POST['experiment_id']) && $_POST['experiment_id']==$row['experiment_id']){echo " selected=\"yes\" ";}
          echo " >".$row['title']."</option>\n";          
        }
?>
      </select>
    </td>
  </tr>
  <tr>
  
    <td>Date:<br><small>(mm/dd/yyyy)</small></td><td>
      <input type="text" class='textInput' name="edate" maxlength="10" value="<?php if ($rememberFormValues!=0 && isset($_POST['edate'])){echo $original_date;} ?>" >
    </td>
    <td>
      <a style='text-decoration:underline; cursor:hand;' onClick="newWindow()">Show calendar</a>
    </td>
  </tr>

<?
  for ($ii=1; $ii<=$iimax; $ii++)
  {
    printTimeslot($ii, $iimax);
  }
?>

  <tr>
    <td colspan=2>
      <input type="submit" name="submit" value="Create the above slots">
      <input type="submit" name="reset" value="Reset">
    </td>
  </tr> 
</table>
</form>

<?php 
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
