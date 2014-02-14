<?php
  $filename="index1.php";
  include "loginCheck.php";  

  // for a normal user just forward
  if($_SESSION['permission']=="participant") {
    header('Location: showall.php');
    die();
  }
  include "header.php";
  echo' <p><font size="3"><b>Main Menu</b></font></p><br>';  
  if(isset($_SESSION['permission']) && ($_SESSION['permission']=="researcher")){
  ?>
   <div id='researcherMenu'>
      <p>Participant</p>
      <a href="showall.php" class="button"><span class="experiment">Available Experiments</span></a>          
   </div>  
   
   <div id='adminMenu'>
    <p>Researcher</p>
       <a href="timeslotedit.php" class="button"><span class="managet">Manage My Timeslots</span></a>
   </div>
   
  <?php
  }
  
  if(isset($_SESSION['permission']) && $_SESSION['permission']=="admin"){
  ?>  
   <div id='researcherMenu'>
      <p>Participant</p>
      <a href="showall.php" class="button"><span class="experiment">Available Experiments</span></a>          
   </div> 
   
   <div id='adminMenu'>
    <p>Admin</p>
       <a href="experimentedit.php" class="button"><span class="managee">Manage Experiments</span></a>
       <a href="managetimeslots.php" class="button"><span class="manageallt">Manage All Timeslots</span></a>
       <a href="useredit.php" class="button"><span class="users">Manage Users</span></a>
       <a href="statistics.php" class="button"><span class="stats">User Statistics</span></a>
       <a href="db_clean.php" class="button"><span class="clean">Database Cleanup</span></a>
	   <a href="email.php?type=experimenters" class="button"><span class="email">Email All Experimenters</span></a>
	   <a href="email.php?type=all" class="button"><span class="email">Email Everyone</span></a>
   </div>   
  <?php
  }  
  include "footer.php";
?>
