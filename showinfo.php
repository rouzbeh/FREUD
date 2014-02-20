<?php
$filename="showinfo.php";

include "loginCheck.php";
require("require.php");
include "connectDB.php";

$messageCode=0;
$message="";

$editmode=0;
?>

<html>

<head>

<link rel="stylesheet" href="style.css" type="text/css" />
<style type="text/css">
          <!--

          table {
padding: 20px;
border: 0px solid red;
width: 100%;
}

table tbody th{
  background-color: rgb(102,153,255);
color: white;
  font-weight: bold;
border: 0px solid white;
}

table tbody tr td{
border: 0px solid white;
padding: 10px 10px;
}

table .lichy{
  background-color: rgb(232,232,232);
}

table .sudy{
  background-color: rgb(240,240,240);
}
//-->
</style>

<script type="text/javascript" langauge="JavaScript">
	<!--
    function onLD() 
{
  focus();
  window.opener.location.reload();
}
//-->
</script>
</head>

<body onLoad="onLD()">

<?php
    //save changes
    if (isset($_POST['submit']))
    {
      //This makes sure they did not leave any fields blank
      if (empty($_POST['title']) || empty($_POST['description']) || empty($_POST['hour_credit']) || empty($_POST['location']) || empty($_POST['title']) || empty($_POST['advisor']) ) 
      {
		$message = 'Some fields are empty - fill them with appropriate information!';
		$messageCode=1;
      }

      if ($messageCode==0)
      {
		if (isset($_POST['isopen']) && $_POST['isopen']=="on") $xisopen=1; else {$xisopen=0;} //processing of checkbox
		if (isset($_POST['iscompleted']) && $_POST['iscompleted']=="on") $xiscompleted=1; else {$xiscompleted=0;} //processing of checkbox

        $stmt = $mysqli->prepare("UPDATE experiment SET title = ?, is_open=?, description = ?, hour_credit = ?, exp_completed=?, location=?, advisor=?, researcher_email=?  WHERE experiment_id = ?");
        if(!$stmt) die("Prepare failed");
        $stmt->bind_param('sisiisssi', $_POST['title'], $xisopen, $_POST['description'], $_POST['hour_credit'], $xiscompleted, $_POST['location'], $_POST['advisor'], $_POST['researcher_email'], $_GET['eid']);
        if (!$stmt->execute()) {
          echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        $stmt->close();
      }
    }

if (isset($_GET['action']))
{
  switch($_GET['action'])
  {
    //simly show the information, no edit available
    case 0:
      {
        echo "<table style='border-collapse:collapse; border: 1px solid #aaa; width: 100%'>\n";
        echo "  <tbody>\n";
        echo "<tr><th colspan='2'>Experiment Detail</th></th>";       

        $stmt = $mysqli->prepare("SELECT * FROM experiment WHERE experiment_id=?");
        if(!$stmt) die("Prepare failed");
        $stmt->bind_param('i', $_GET['eid']);
        if (!$stmt->execute()) {
          echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        echo "    <tr class='sudy'>\n";
        echo "      <td>";
        echo "title:";        
        echo "</td>\n";

        echo "      <td>";
        echo $row['title'];
        echo "</td>\n";
        echo "    </tr>\n";

        echo "    <tr class='lichy'>\n";
        echo "      <td>";
        echo "description:";        
        echo "</td>\n";

        echo "      <td>\n";
        echo $row['description']."\n";
        echo "      </td>\n";
        echo "    </tr>\n";

        echo "    <tr  class='sudy'>\n";
        echo "      <td>";
        echo "hour/credit:";        
        echo "</td>\n";

        echo "      <td>";
        echo $row['hour_credit'];
        echo "</td>\n";
        echo "    </tr>\n";

        echo "    <tr class='lichy'>\n";
        echo "      <td>";
        echo "location:";        
        echo "</td>\n";

        echo "      <td>";
        echo $row['location'];
        echo "</td>\n";
        echo "    </tr>\n";

        echo "    <tr>\n";
        echo "      <td>";
        echo "researcher email:";        
        echo "</td>\n";

        echo "      <td>";
        echo $row['researcher_email'];
        echo "</td>\n";
        echo "    </tr>\n";

        echo "    <tr class='lichy'>\n";
        echo "      <td>";
        echo "advisor:";        
        echo "</td>\n";

        echo "      <td>";
        echo $row['advisor'];
        echo "</td>\n";
        echo "    </tr>\n";


        echo "  </tbody>\n";
        echo "</table>\n";
        break;
      }

      //edit information
    case 1:
      {

        $listofresearchers=array();

        $query = "SELECT email FROM user WHERE role='researcher' OR role='admin' ORDER BY `user`.`email`ASC";
        $result = $mysqli->query($query) or die($mysqli->error);
        while($row = mysqli_fetch_array($result)) 
        {
          array_push($listofresearchers, $row['email']);
        }

        echo "<form method=\"post\" action=\"\" id=\"projectform\">\n";
        $stmt = $mysqli->prepare("SELECT * FROM experiment WHERE experiment_id=?");
        if(!$stmt) die("Prepare failed");
        $stmt->bind_param('i', $_GET['eid']);
        if (!$stmt->execute()) {
          echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        echo "<label>title:</label>\n";
        echo "<input type=\"text\" name=\"title\" maxlength=\"50\" value=\"".$row['title']."\">";
        echo "<label>description:</label>\n";        
        echo "<textarea name=\"description\" rows=\"20\" cols=\"50\">".$row['description']."</textarea>";
        echo "<label>hour/credit:</label>\n";        
        echo "<input type=\"text\" name=\"hour_credit\" maxlength=\"20\" value=\"".$row['hour_credit']."\">";
        echo "<label>location:</label>\n";        
        echo "<input type=\"text\" name=\"location\" maxlength=\"50\" value=\"".$row['location']."\">";
        echo "<label>is open:</label>\n";        
        echo "<input type=\"checkbox\" name=\"isopen\"";
        if ($row['is_open']==1) echo "checked";
        echo ">";
        echo "<label>is completed:</label>\n";        
        echo "<input type=\"checkbox\" name=\"iscompleted\"";
        if ($row['exp_completed']==1) echo "checked";
        echo ">";
        echo "<label>researcher email:</label>\n";        
        echo "<select name=\"researcher_email\">";
        //offer list of all possible researchers
        while(list($key, $value) = each($listofresearchers)) 
        {
          echo "<option ";
          if (strcasecmp($value, $row['researcher_email'])==0) echo "selected=\"yes\"";
          echo ">".$value."</option>";
        }
        echo "<label>advisor:</label>\n";
        echo "<input type=\"text\" class=\"textInput\"  name=\"advisor\" maxlength=\"3\" value=\"".$row['advisor']."\">";
        echo "<input type=\"submit\" name=\"submit\" value=\"Save changes\">\n";
        echo "</form>\n";
        break;
      }

  }
}

echo "<p><center><a href=\"\" onclick=\"window.close()\">Close window</a></center>\n"

?>
</body></html>
