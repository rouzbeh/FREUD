<?php
  $filename="register.php";  
  require("require.php");
  include "connectDB.php";
  $message="";
  $messageCode=0;

  //This code runs if the form has been submitted
  if (isset($_POST['submit'])) 
  { 
    //This makes sure they did not leave any fields blank
    if (empty($_POST['email']) || empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['passw1']) || empty($_POST['passw2']) ) 
    {
      $message = 'You did not complete all of the required fields';
      $messageCode=1;
    }

    //if (!eregi("[a-zA-Z0-9](@" . $email_domain . ")", $_POST['email']))
    //{
    //  $message = 'Invalid email address';
    //  $messageCode=2;
    //}

    //if all information entered, no empty fields
    if ($messageCode==0)
    {
      // checks if the username is in use
      if (!get_magic_quotes_gpc()) 
      {
        $_POST['email'] = addslashes($_POST['email']);
      }
      $emailcheck = $_POST['email'];
      $check = mysqli_query("SELECT email FROM user WHERE email = '$emailcheck'") ;//or die(mysqli_error($connectionDB));
echo $check;?>
fnjsdklfhjs
<?php
      $check2 = mysqli_num_rows($check);

      //if the name exists it gives an error
      if ($check2 != 0) 
      {
        $message = "Sorry, the user with email address '".$_POST['email']."' is already in use.";
        $messageCode=3;
      }
    }
    
    //if both entered and re-entered password matches 
    if ($messageCode==0)
    {
      // this makes sure both passwords entered match
      if ($_POST['passw1'] != $_POST['passw2']) 
      {
        $message = "Your passwords did not match.";
        $messageCode=4;
      }
    }
    
    if ($messageCode==0)
    {
      // here we encrypt the password and add slashes if needed
      $_POST['passw1'] = md5($_POST['passw1']);
      if (!get_magic_quotes_gpc()) 
      {
        $_POST['passw1'] = addslashes($_POST['passw1']);
        $_POST['email'] = addslashes($_POST['email']);
        $_POST['name'] = addslashes($_POST['name']);
        $_POST['surname'] = addslashes($_POST['surname']);
      }
  
      if (isset($_POST['receiveMail']) && $_POST['receiveMail']=="on") $receive=1;
      else $receive=0;
      
      $validUser=substr(md5($_POST['email'].time()),0, 19);
      
      //now we insert it into the database 
      $query = "INSERT INTO user VALUES ('".$_POST['email']."', '".$_POST['name']."', '".$_POST['surname']."', '".$_POST['passw1']."', 'participant','".$receive."', '".$validUser."', '".$_POST['classyear']."' )";
      $result = mysqli_query($connectionDB, $query) or die(mysqli_error($connectionDB));
    

      //Now we let them know if their registration was successful
      $message="You are now registered!  Welcome to FREUD.";
      $messageCode=5;

 
      $email=$_POST['email'];
      $subject="Registration for FREUD Online";
      $emailmessage="<html>Please click the following link to confirm your registration.<br><a href=\"".$internetLocation."confirmRegistration.php?val=".$validUser."\">".$internetLocation."confirmRegistration.php?val=".$validUser."</a>";
      $emailmessage=$emailmessage."<br><br>generated by UCITS<br></html>";
      $headers = 'From: ' .  $server_email_address . "\r\n";  
      $headers.= "Content-Type: text/html; charset=ISO-8859-1 ";
      $headers.= "MIME-Version: 1.0 "; 
	  
      //send an email
      mail($email,$subject,$emailmessage, $headers);
//echo $emailmessage."<br>";

      $myFile = "confirmreg.html";
      $fh = fopen($myFile, 'w');
      fwrite($fh, $email."<br>");
      fwrite($fh, $subject."<br>");
      fwrite($fh, $emailmessage."<br>");
      fclose($fh);

    }

  } 
  include "header.php";
?>

  <font size="4"><b>Registration</b></font>
<br /><br /><br />
    <!-- Form with text fields etc. -->
    <form action="" method="post">
      <table style='width:auto'>
        
        <tr><th colspan='2'>New User</tr>
        <tr>
          <td>Email</td>
          <td>
            <input type="text" class='textInput' name="email" maxlength="40" value="<?php if ($messageCode!=0){echo $_POST['email'];} ?>">
          </td>
        </tr>
        <tr>
          <td>First name:</td><td>
            <input type="text" name="name" class='textInput' maxlength="20" value="<?php if ($messageCode!=0){echo $_POST['name'];} ?>">
          </td>
        </tr>
        <tr>
          <td>Last name:</td>
          <td>
            <input type="text" name="surname"  class='textInput' maxlength="20" value="<?php if ($messageCode!=0){echo $_POST['surname'];} ?>">
          </td>
        </tr>
        <tr>
          <td>Password:</td>
          <td>
            <input type="password" name="passw1"  class='textInput' maxlength="20">
          </td>
        </tr>
        <tr>
          <td>Re-enter password:</td>
          <td>
            <input type="password" name="passw2" class='textInput'  maxlength="20">
          </td>
        </tr>
        <tr>
          <td>Class year:</td>
          <td>
            <select name="classyear">
<?
              $datenow=date("Y");
              for ($ii=0; $ii<5; $ii++)
              {
                $datenow1=$datenow+$ii;
                echo "<option value=\"".$datenow1."\" ";
                if ($messageCode!=0){ if (isset($_POST['classyear']) && strcasecmp($_POST['classyear'], $datenow1)==0) echo "selected";}
                echo ">".$datenow1."</option>\n";
              }
?>
            </select>
          </td>
        </tr>
        <tr>
          <td>Check this box to receive <br>weekly emails to notify you of <br>all the research <br>opportunities available:</td>
          <td>
            <input type="checkbox" name="receiveMail" <?php if ($messageCode!=0){ if (isset($_POST['receiveMail']) && $_POST['receiveMail']=="on") echo "checked";} ?>>
          </td>
        </tr>
        <tr>
          <td colspan=2>
            <input type="submit" name="submit" value="Register">
            <input type="submit" name="back" value="Back">
          </td>
        </tr> 
      </table>
    </form>

<?php 
//} 
  include "disconnectDB.php";
  
  if ($messageCode!=0)
  {
    echo "<center><font color=\"red\"><b>".$message."</b></font></center>\n";
  }
  include "footer.php";  
?>
