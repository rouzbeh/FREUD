<?php
require("require.php");
include "loginCheck.php";

include "connectDB.php";
if(isset($_SESSION['permission']) && ($_SESSION['permission']=="admin")){
	$messageCode=0;
	$message="";
	include "header.php"; 
	echo "<div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div>"; 

	if(isset($_POST['submit'])){
		$to = $_POST['to'];
		$subject = $_POST['subject'];
		$message = $_POST['message'];
		$from = $_POST['from'];
		$reply = $_POST['from'];

		$headers = "From: " . $from . "\r\n";
		$headers .= "Reply-to: "  . $from . "\r\n";
		$headers .= "Return-Path:" . $from . "\r\n";
		$headers .= "Bcc: " . $_POST['bcc'] . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

		mail($to,$subject,$message,$headers);
		echo "Email Sent!";
	}
	if($_GET['type']=="experimenters"){

		$query = "SELECT email FROM user WHERE role='researcher' ORDER BY `user`.`email`DESC";
		$result = mysqli_query($connectionDB, $query) or die(mysqli_error($connectionDB));
		$experimenter_email_list = '';
		while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
		{
			$experimenter_email_list = $row['email'] . ", " . $experimenter_email_list;
		}
		?>
			Send an email to experimenters.
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<table>
			<tr>
			<td>To:</td>
			<td><textarea name="to" rows="1" cols="50"></textarea></td>
			</tr>
			<tr>
			<td>BCC:</td>
			<td><textarea name="bcc" rows="5" cols="50"><?php echo $experimenter_email_list ?></textarea></td>
			</tr>
			<tr>
			<td>Subject:</td>
			<td><input type="text" name="subject" size="67" maxlength="100" value=""></td>
			</tr>
			<tr>
			<td>Message:</td>
			<td><textarea name="message" rows="7" cols="50"></textarea></td>
			</tr>
			<tr>
			<td>From:</td>
			<td>
			<select name="from">
			<option><?php echo $server_email_address; ?></option>
			<?php
			$query = "SELECT email FROM user WHERE role='admin' ORDER BY `user`.`email`ASC";
		$result = mysqli_query($connectionDB, $query) or die(mysqli_error($connectionDB));
		while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
		{
			echo "<option>" . $row['email'] . "</option>\n";
		}
		?>

			</select>
			</td>
			</tr>
			<tr>
			<td>
			<input type="submit" name="submit" value="Send!">
			</td>
			</tr>
			</table>
			</form>
			<?php
	} elseif($_GET['type']=="all"){

		$query = "SELECT email FROM user ORDER BY `user`.`email`DESC";
		$result = mysqli_query($connectionDB, $query) or die(mysqli_error($connectionDB));
		$email_list = '';
		while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
		{
			$email_list = $row['email'] . ", " . $email_list;
		}
		?>
			Send an email to EVERYONE.
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<table>
			<tr>
			<td>To:</td>
			<td><textarea name="to" rows="1" cols="50"></textarea></td>
			</tr>
			<tr>
			<td>BCC:</td>
			<td><textarea name="bcc" rows="5" cols="50"><?php echo $email_list ?></textarea></td>
			</tr>
			<tr>
			<td>Subject:</td>
			<td><input type="text" name="subject" size="67" maxlength="100" value=""></td>
			</tr>
			<tr>
			<td>Message:</td>
			<td><textarea name="message" rows="7" cols="50"></textarea></td>
			</tr>
			<tr>
			<td>From:</td>
			<td>
			<select name="from">
			<?php
			$query = "SELECT email FROM user WHERE role='admin' ORDER BY `user`.`email`ASC";
		$result = mysqli_query($connectionDB, $query) or die(mysqli_error($connectionDB));
		while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
		{
			echo "<option>" . $row['email'] . "</option>\n";
		}
		?>
			</select>
			</td>
			</tr>
			<tr>
			<td>
			<input type="submit" name="submit" value="Send!">
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
