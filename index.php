<?php
$filename="index.php";
require("require.php");
include "loginCheck.php";
  
include "connectDB.php";

$messageCode=0;
$message="";
if(array_key_exists('timeout', $_GET)){
  if(($_GET['error'])=='timeout'){
    $message = "Your session has timed out";
    $messageCode=10;
  }
}
  
//This code runs if the form has been submitted
if (isset($_POST['submit'])) 
{ 
  //is our username(email) in database?
  if (!($stmt = $mysqli->prepare("SELECT * FROM user WHERE email=?"))) {
    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $stmt->bind_param("s", $_POST['email']);
  if (!$stmt->execute()) {
    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $result = $stmt->get_result();
  $stmt->close();

  $check=mysqli_num_rows($result);
  
  if ($check != 1) 
  {
    $message = "Username not found.  Please <a href=\"register.php\">register</a>";
    $messageCode=1;
  }
      
  //check if passwords match
  if ($messageCode==0 || $messageCode==10)
  {
    $row = mysqli_fetch_array($result);
      
    //create session variables, if passwords match
    if (password_verify($_POST['passw1'],$row['password']))
    {
      $_SESSION['isConnected']=1;
      $_SESSION['email'] = $_POST['email'];
      $_SESSION['permission'] = $row['role'];
      $_SESSION['timestamp'] = time();
      //print a message, if passwords don't match
    }else{
      $message = "Sorry, wrong password.";
      $messageCode=2;
    }
  }

}

//if already logged in, go to next index page
if (isset($_SESSION['isConnected']))
{
  header("Location: index1.php");
}
include "header.php";
?>  
<!-- bellow there is a space for a comment-->
 
<div id='loginWrapper'>            
    <div id='loginL'>
    <font size="2"> 
    <!-- type the comment here-->
  
<?php echo $welcome_message; ?>
  
    <!-- type the comment here-->
    </font>
    </div>  
    <div id='loginR'>  
    <form id='loginform' action="" method="post">
    <table>
<?php
    if ($messageCode!=0){
      echo "<center id='loginErr'><font color=\"red\"><b>".$message."</b></font></center>\n";
    }
?>
<tr>
<td>Email:</td>
<td ><input type="text" id="textinput" name="email" class="textinput" maxlength="25" /></td>
    </tr>
    <tr>
    <td>Password:</td>
    <td ><input type="password" id="passwordinput" name="passw1" class="textinput" maxlength="25" /></td>
    </tr> 
    <tr>
    <td>&nbsp;</td>
<td>&nbsp;</td> 
<td><input type="submit" name="submit" value="Login" class="buttonSubmit" /></td>
    </tr>       
    </table>       
    </form>  
		
    <a id='reg' href="register.php">Register</a>
    <a id='forgot' href="forgotlogininfo.php">I can't log in</a> 
</div>
</div>

<?php  
  
  include "disconnectDB.php";
  
  include "footer.php";
?>
