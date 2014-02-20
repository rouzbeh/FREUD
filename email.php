<?php
$filename="mail.php";
require("require.php");
// Pear Mail Library
require_once "Mail.php";
include "loginCheck.php";
include "connectDB.php";

function send_email($to, $headers, $body) {
  global $SMTPserver;
  global $SMTPPort;
  global $SMTPAuth;
  global $SMTPUsername;
  global $SMTPPassword;

  $smtp = Mail::factory('smtp', array(
      'host' => $SMTPserver,
      'port' => $SMTPPort,
      'auth' => $SMTPAuth,
      'username' =>  $SMTPUsername,
      'password' => $SMTPPassword));
  $mail = $smtp->send($to, $headers, $body);
  if (PEAR::isError($mail)) {
    echo("<p>" . $mail->getMessage() . "</p>");
  } else {
    echo("Email sent!");
  }
}

if(isset($_SESSION['permission']) && ($_SESSION['permission']=="admin")){
  $messageCode=0;
  $message="";
  include "header.php"; 
  echo "<div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div>"; 

  if(isset($_POST['submit'])){
    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    //$from = $_POST['from'];
    $from = $SMTPUsername;
    //$reply = $_POST['from'];
    $reply = $SMTPUsername;

    $headers = array (
        'From' => $from,
        'To' => $to,
        'Bcc' => $_POST['bcc'],
        'Subject' => $subject);

    send_email($to,$headers,$message);
  }
  else {
    $query = "SELECT email FROM user WHERE role='researcher' ORDER BY `user`.`email`DESC";
    $result = $mysqli->query($query) or die($mysqli->error);
    $experimenter_email_list = '';
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
      $experimenter_email_list = $row['email'] . ", " . $experimenter_email_list;
    }
    $query = "SELECT email FROM user ORDER BY `user`.`email`DESC";
    $result = $mysqli->query($query) or die($mysqli->error);
    $email_list = '';
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
    {
      $email_list = $row['email'] . ", " . $email_list;
    }
?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="emailForm">
    <div>
    <h1>Send an email to <?php
        if(isset($_GET['type']) && $_GET['type']=="experimenters"){
      echo "Experimenters";
    }
    elseif (isset($_GET['type']) && $_GET['type']=="all") {
      echo "EVERYONE";
    }?></h1>
    <label for="to">To:</label>
    <textarea id="to" name="to" class='textInput' rows="1" cols="50"></textarea>
    
    <label for="bcc">BCC:</label>
    <textarea id="bcc" name="bcc" class='textInput' rows="5" cols="50"><?php
    if(isset($_GET['type']) && $_GET['type']=="experimenters"){
      echo $experimenter_email_list;
    }
    elseif (isset($_GET['type']) && $_GET['type']=="all") {
      echo $email_list;
    }?></textarea>
    
    <label for="subject">Subject:</label>
    <input type="text" id="subject" name="subject" class='textInput' size="60" maxlength="100" value="" />
    
    <label for="message">Message:</label>
    <textarea id="message" name="message" class='textInput' rows="7" cols="50"></textarea>
    <input class="mySubmit" type="submit" name="submit" value="Send!">
    </div>
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
