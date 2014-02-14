<?php
  $filename="useredit.php";

  include "loginCheck.php";
  
  include "connectDB.php";
if(isset($_SESSION['permission']) && ($_SESSION['permission']=="admin")){

  $messageCode=0;
  $message="";
  
  $editmode=0;
  
  //data manipulation
  if (isset($_GET['action']))
  {
    // action=0 ... edit mode
    // action=1 ... save change mode
    // action=2 ... reset password mode
    // action=3 ... remove user mode
    // action=4 ... multiple removal from database using checkboxes
    switch($_GET['action'])
    {
      case 0:
      {
        //edit mode
        $editmode=1;
        break;
      }
      case 1:
      {
        //save changes mode
        if (!get_magic_quotes_gpc()) 
        {
          $_POST['password'] = addslashes($_POST['password']);
          $_POST['name'] = addslashes($_POST['name']);
          $_POST['surname'] = addslashes($_POST['surname']);
        }
        if (isset($_POST['receiveMail']) && $_POST['receiveMail']=="on") $xreceiveMail=1; else {$xreceiveMail=0;} //processing of checkbox

        $query = "UPDATE user SET name = '".$_POST['name']."', surname = '".$_POST['surname']."',  role='".$_POST['role']."', receiveMail='".$xreceiveMail."', classyear='".$_POST['classyear']."' WHERE email = '".$_GET['id']."'";
        $result = mysql_query($query) or die(mysql_error());
        break;
      }
      case 2:
      {
        //reset password to default
        $query = "UPDATE user SET password = '" . $default_password . "' WHERE email = '".$_GET['id']."'";
        $result = mysql_query($query) or die(mysql_error());

        $messageCode=1;
        $message="Password for ".$_GET['id']." changed '" . $the_default_password . "'";

        break;
      }
      case 3:
      {
        //remove user from DB
        $query = "DELETE FROM user WHERE email = '".$_GET['id']."'";
        $result = mysql_query($query) or die(mysql_error());

        $messageCode=2;
        $message="A user with an email address ".$_GET['id']." was removed from the database.";

        break;
      }
      case 4:
      {
        for($ii=0; $ii<$_POST['usersTotal']; $ii++)
        {
          $checkboxname="checkbox".$ii;
          if (isset($_POST[$checkboxname]) && $_POST[$checkboxname]=="on")
          {
            //delete user info
            $query = "DELETE FROM user WHERE email = '".$_POST["h".$checkboxname]."'";
            $result = mysql_query($query) or die(mysql_error());

            //delete all requests and signsups
            $query = "DELETE FROM request WHERE participant_email = '".$_POST["h".$checkboxname]."'";
            $result = mysql_query($query) or die(mysql_error());

            $query = "DELETE FROM signsup WHERE participant_email = '".$_POST["h".$checkboxname]."'";
            $result = mysql_query($query) or die(mysql_error());

          }
        }
      
        break;
      }
    }
  }


  include "header.php";
  echo "<div class='goBackTop'>&lt; <a href='index1.php'>Back</a></div>";   
  echo "<font size='3' style='display:block; padding: 25px 10px 10px 0px;'><b>Manage user accounts</b></font>";
  

  //form used only for saving changed data, therefore action=1
  if (isset($_GET['id']))
  {
    echo "<form id='userform' action=\"?id=".$_GET['id']."&action=1\" method=\"post\">\n";
  }else{
    echo "<form id='userform' action=\"?action=4\" method=\"post\">\n";
  }

  //generate table with all the data
  echo "<table id='usertable'>\n";
  echo "  <tbody>\n";
  echo "    <tr>\n";
  echo "      <th>&nbsp</th>";
  echo "      <th>email</th>\n";
  echo "      <th>name</th>\n";
  echo "      <th>surname</th>\n";
  echo "      <th>classyear</th>\n";
  //echo "      <th>password</th>\n";
  echo "      <th>role</th>\n";
  echo "      <th>mail?</th>\n";
  //echo "      <th>valid?</th>\n";
  echo "      <th >Actions</th>\n";
  echo "    </tr>\n";

  $query = "SELECT * FROM user ORDER BY email ASC";
  $result = mysql_query($query) or die(mysql_error());
  $num = mysql_num_rows($result)-1;
  $id=0;
  
  $counter4 = 0;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC))
  {
    if(($counter4++)%2==0)  echo "  <tr class='sudy'>\n";
    else                   echo "  <tr class='lichy'>\n";
    if ($editmode==1 && $row['email']==$_GET['id'] && strcasecmp($row['email'], $_SESSION['email'])!=0)
    {
      //when editing data
      echo "      <td>&nbsp</td>\n";
      echo "      <td>".$row['email']."</td>";
      echo "      <td><input type=\"text\" class='textInput' name=\"name\" maxlength=\"20\" size=\"10\" value=\"".$row['name']."\"></td>\n";
      echo "      <td><input type=\"text\" class='textInput'  name=\"surname\" maxlength=\"20\" size=\"10\" value=\"".$row['surname']."\"></td>\n";

      echo "      <td>\n";
      echo "        <select name=\"classyear\">\n";
      $datenow=date("Y");
      for ($ii=0; $ii<4; $ii++)
      {
          $datenow1=$datenow+$ii;
          echo "          <option value=\"".$datenow1."\" ";
          if ($messageCode!=0){ if (isset($_POST['classyear']) && strcasecmp($_POST['classyear'], $datenow1)==0) echo "selected";}
          echo ">".$datenow1."</option>\n";
      }
      echo "        </select>\n";
      echo "      </td>\n";
      
      //echo "      <td><input type=\"text\" name=\"password\" maxlength=\"50\"  size=\"30\" value=\"".$row['password']."\"></td>\n";
      echo "      <td>\n";
      switch ($row['role'])
      {
        case "participant":
        {
            echo "        <select name=\"role\">\n";
            echo "          <option selected=\"yes\">participant</option>\n";
            echo "          <option>researcher</option>\n";
            echo "          <option>admin</option>\n";
            echo "        </select>\n";
          break;
        }
        case "researcher":
        {
            echo "        <select name=\"role\">\n";
            echo "          <option>participant</option>\n";
            echo "          <option selected=\"yes\">researcher</option>\n";
            echo "          <option>admin</option>\n";
            echo "        </select>\n";
          break;
        }
        case "admin":
        {
            echo "        <select name=\"role\">\n";
            echo "          <option>participant</option>\n";
            echo "          <option>researcher</option>\n";
            echo "          <option selected=\"yes\">admin</option>\n";
            echo "        </select>\n";
          break;
        }
      }
      echo "     </td>\n";
      echo"     <td><input type=\"checkbox\" name=\"receiveMail\"";
      if ($row['receiveMail']==1) echo "checked";
      echo "></td>\n";
      $outputValid = $row['validUser'];//($row['validUser']=='0')? "YES" : "NO";
      //echo "      <td>$outputValid</td>\n";
      echo "      <td><nobr><input type=\"submit\" name=\"save\" value=\"Save\">\n";
      echo "      <a href=\"?id=".$row['email']."&action=4\"><input type='button' value='Cancel'></a></nobr>\n";           
    }else{
      //if we are NOT editing data
      if ($_SESSION['email'] != $row['email'])
      {
        echo "      <td><input type=\"checkbox\" name=\"checkbox".$id."\"><input type=\"hidden\" name=\"hcheckbox".$id."\" value=\"".$row['email']."\"></td>\n";
        $id=$id+1;
      }else{
        echo "      <td>&nbsp</td>\n";
      }
      echo "      <td>".$row['email']."</td>\n";
      echo "      <td>".$row['name']."</td>\n";
      echo "      <td>".$row['surname']."</td>\n";
      echo "      <td>".$row['classyear']."</td>\n";
      //echo "      <td>".$row['password']."</td>\n";
      echo "      <td>".$row['role']."</td>\n";
      echo "      <td>";
      if ($row['receiveMail']==1)
      {
        echo "YES";
      }else{
        echo "NO";
      }
      echo "</td>\n";
      
      $validd = ($row['validUser']=='0')? "YES" : "NO";
      //echo "      <td>".$validd."</td>\n";
      
      
      if (strcasecmp($row['email'], $_SESSION['email'])!=0) //prevent from editing/deleting yourself
      {
        echo "      <td><nobr><a class='noborder' href=\"?id=".$row['email']."&action=0\"><img src='images/edit_icon.gif' alt='Edit' title='Edit' height='16' width='15'></a>\n";
        echo "      &nbsp;&nbsp;<a class='noborder' onclick='return confirm(\"Are you sure you want to reset password of this user?\");' href=\"?id=".$row['email']."&action=2\"><img src='images/key_icon.gif' alt='Reset password' title='Reset password' height='16' width='16'></a>\n";
        echo "      <a class='noborder' onclick='return confirm(\"Are you sure you want to delete this user?\");' href=\"?id=".$row['email']."&action=3\"><img src='images/delete_icon.gif' alt='Remove' title='Remove' height='15' width='15'></a></nobr></td>\n";
      }else{  
        echo "      <td>&nbsp;</td>\n";
      }
    }
  }
  
  echo "    </tr>\n";
  echo "    <tr>\n";
  echo "      <td colspan=\"12\" align=\"center\"><input type=\"hidden\" name=\"usersTotal\" value=\"".$num."\"><input type=\"submit\" name=\"deleteselected\" value=\"Delete selected\"></td>\n";
  echo "    </tr>\n";  
  echo "  </tbody>\n";
  echo "</table>\n";
  echo "</form>\n";

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
