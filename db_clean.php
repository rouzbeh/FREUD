<?php
	$filename="useredit.php";

	include "loginCheck.php";
  
	include "connectDB.php";


	$messageCode=0;
	$message="";
  
	$editmode=0;
  
	include "header.php";
    
	if(isset($_SESSION['permission']) && $_SESSION['permission']=="admin"){
		//data manipulation
		if (isset($_REQUEST['action'])){
		
		// action=0 ... clean mode
	   
			if($_REQUEST['action']==1){   
			   
				//save changes mode
				if (!get_magic_quotes_gpc()) {
					$_POST['experiment'] = addslashes($_POST['experiment']);
					$_POST['request'] = addslashes($_POST['request']);
					$_POST['signsup'] = addslashes($_POST['signsup']);
					$_POST['timeslot'] = addslashes($_POST['timeslot']);
					$_POST['user'] = addslashes($_POST['user']);
				}
				echo "<div class='goBackTop'>&lt; <a href='db_clean.php'>Back</a></div>";
				echo "<font size='3' style='display:block; padding: 25px 10px 10px 0px;'><b>Confirm deleting:</b></font>";    
			  
				if($_POST['experiment']) echo "<b style='line-height: 1.8em; '>delete experiments...<br />"; 
				if($_POST['request'])    echo "delete requests...<br />"; 
				if($_POST['signsup'])    echo "delete signsups...<br />"; 
				if($_POST['timeslot'])   echo "delete timeslots...<br />"; 
				if($_POST['user'])       echo "delete users (not admins)...<br /></b>"; 
				if($_POST['grad'])       echo "delete graduated users...<br /></b>"; 
?> 
				<form action="db_clean.php?action=2" method="post">        
			
$mysqli->query(					<input type="hidden" name="experiment" value="<?php echo $_POST['experiment'];?>">
					<input type="hidden" name="request" value="<?php echo $_POST['request'];?>">
					<input type="hidden" name="signsup" value="<?php echo $_POST['signsup'];?>">
					<input type="hidden" name="timeslot" value="<?php echo $_POST['timeslot'];?>">
					<input type="hidden" name="user" value="<?php echo $_POST['user'];?>">
					<input type="hidden" name="grad" value="<?php echo $_POST['grad'];?>">
					<br /><input type="submit" value="Delete" style="margin-left:130px">
				</form>
				<br /><br />
				<form action="db_clean.php" method="post">
					<input type="submit" value="Cancel" style="margin-left:130px">
				</form>
				<br /><br />
<?php
			}  elseif($_REQUEST['action']==2)  {   
	
				if($_POST['experiment']) {           
					echo "<b style='line-height: 1.8em; '>deleted: experiments<br />";
					$query = "DELETE FROM experiment";
					//echo $query;
					$result = $mysqli->query($query) or die(mysqli_error($connectionDB)); 
				}
			
				if($_POST['request'])   { 
					echo "deleted: requests...<br />";
					$query = "DELETE FROM request";
					//echo $query; 
					$result = $mysqli->query($query) or die($mysqli->error); 
				} 
				if($_POST['signsup'])   { 
					echo "deleted: signsups...<br />"; 
					$query = "DELETE FROM signsup";
					//echo $query;
					$result = $mysqli->query( $query) or die($mysqli->error); 
				}
			
				if($_POST['timeslot'])  { 
					echo "deleted: timeslots...<br />";
					$query = "DELETE FROM timeslot";
					//echo $query;
					$result = $mysqli->query($query) or die($mysqli->error); 
				}
			
				if($_POST['user'])      { 
					echo "deleted: users...<br />";
					$query = "DELETE FROM user WHERE role!='admin'";            
					//echo $query;
					$result = $mysqli->query($query) or die($mysqli->error);  
				}
				if($_POST['grad'])      { 
					echo "deleted: graduated users...<br />";
					if(date(n) < 6){
						$grad_year = (date(Y) - 1);
					} else {
						$grad_year = date(Y);
					}
					$query = "DELETE FROM user WHERE classyear <= '" . $grad_year . "' AND role != 'admin'";            
					$result = $mysqli->query($query) or die($mysqli->error);  
				}
			
				echo "</b><br /><br />All done, return to <a href='index1.php'>Main page</a><br /><br />";        

			}    
		  
		} else {
			$_REQUEST['action'] = 1;
			echo "<div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div>";  
			echo "<font size='3' style='display:block; padding: 25px 10px 10px 0px;'><b>Delete all records from the following tables:</b></font>";
?>
			<br>
			<form action="db_clean.php?action=<?php echo $_REQUEST['action'];?>" method="post">   
				<table style='width:auto'>
					<tr>
						<th>Table Name</th>
						<th>Clean</th>
					</tr>
					<tr>
						<td>experiments:</td><td>
							<input type="checkbox" name="experiment" checked="checked">
						</td>
					</tr>
					<tr>
						<td>requests:</td><td>
						<input type="checkbox" name="request" checked="checked">
						</td>
					</tr>
					<tr>
						<td>signsups:</td><td>
						<input type="checkbox" name="signsup" checked="checked">
						</td>
					</tr>
					<tr>
						<td>timeslots:</td><td>
						<input type="checkbox" name="timeslot" checked="checked">
						</td>
					</tr>
					<tr>
						<td>users (not admins)</td><td>
						<input type="checkbox" name="user" >
						</td>
					</tr>  
					<tr>
						<td>graduated users</td><td>
						<input type="checkbox" name="grad" >
						</td>
					</tr>  
				</table>
				<br /><input type="submit" value="Delete" style="margin-left:130px">
			</form>
<?php
	  
		}
	} else {
		echo "You are not authorized to access this page.";
	}
	include "disconnectDB.php";
	  
	if ($messageCode!=0){
		echo "<div class='generalErr'><b>$message</b></div>\n";
	}
	include "footer.php";
?>
