<?php
require("require.php");
error_reporting(E_ALL);
$conn = mysqli_connect($servername, $username, $password, $databasename) or die(mysqli_error());
//mysqli_select_db($databasename, $conn) or die(mysqli_error());
$query = "
 CREATE  TABLE  `" . $databasename . "`.`experiment` (  `experiment_id` int( 11  )  NOT  NULL  AUTO_INCREMENT ,
 `title` varchar( 50  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 `is_open` int( 11  )  DEFAULT NULL ,
 `description` text COLLATE latin1_general_ci,
 `hour_credit` varchar( 20  )  COLLATE latin1_general_ci  DEFAULT NULL ,
 `exp_completed` int( 11  )  DEFAULT NULL ,
 `location` varchar( 50  )  COLLATE latin1_general_ci  DEFAULT NULL ,
 `researcher_email` varchar( 40  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 `advisor` varchar( 3  )  COLLATE latin1_general_ci NOT  NULL ,
 PRIMARY  KEY (  `experiment_id`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = latin1 COLLATE  = latin1_general_ci;
 ";
mysqli_query($conn, $query) or die(mysqli_error());
$query = " 
 CREATE  TABLE  `" . $databasename . "`.`request` (  `request_id` int( 11  )  NOT  NULL  AUTO_INCREMENT ,
 `timeslot_id` int( 11  )  NOT  NULL DEFAULT  '0',
 `participant_email` varchar( 40  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 PRIMARY  KEY (  `request_id`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = latin1 COLLATE  = latin1_general_ci;
  ";
  mysqli_query($conn, $query) or die(mysqli_error());
 $query = "
 CREATE  TABLE  `" . $databasename . "`.`signsup` (  `sign_up_id` int( 11  )  NOT  NULL  AUTO_INCREMENT ,
 `request_id` int( 11  )  NOT  NULL DEFAULT  '0',
 `participant_email` varchar( 40  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 `timeslot_id` int( 11  )  NOT  NULL DEFAULT  '0',
 PRIMARY  KEY (  `sign_up_id`  ) ,
 UNIQUE  KEY  `participant_email` (  `participant_email` ,  `timeslot_id`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = latin1 COLLATE  = latin1_general_ci;
  ";
  mysqli_query($conn, $query) or die(mysqli_error());
 $query = "
 CREATE  TABLE  `" . $databasename . "`.`timeslot` (  `timeslot_id` int( 11  )  NOT  NULL  AUTO_INCREMENT ,
 `experiment_id` int( 11  )  NOT  NULL DEFAULT  '0',
 `edate` date NOT  NULL DEFAULT  '0000-00-00',
 `etime` time NOT  NULL DEFAULT  '00:00:00',
 `duration` int( 11  )  NOT  NULL DEFAULT  '0',
 `capacity_total` int( 11  )  NOT  NULL DEFAULT  '0',
 PRIMARY  KEY (  `timeslot_id`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = latin1 COLLATE  = latin1_general_ci;
  ";
 mysqli_query($conn, $query) or die(mysqli_error()); 
 $query = "
 CREATE  TABLE  `" . $databasename . "`.`user` (  `email` varchar( 40  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 `name` varchar( 20  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 `surname` varchar( 20  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 `password` varchar( 50  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 `role` varchar( 20  )  COLLATE latin1_general_ci NOT  NULL DEFAULT  '',
 `receiveMail` int( 11  )  NOT  NULL DEFAULT  '0',
 `validUser` varchar( 20  )  COLLATE latin1_general_ci  DEFAULT NULL ,
 `classyear` int( 11  )  DEFAULT NULL ,
 PRIMARY  KEY (  `email`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = latin1 COLLATE  = latin1_general_ci;
  ";
 mysqli_query($conn, $query) or die(mysqli_error());
 $query = "
 INSERT INTO `" . $databasename . "`.`user` (`email`, `name`, `surname`, `password`, `role`, `receiveMail`, `validUser`, `classyear`) VALUES ('" . $server_email_address . "', 'Admin', 'Admin', '" . $default_password . "', 'admin', '1', '0', 'admin');
 ";
 mysqli_query($conn, $query) or die(mysqli_error());
echo "Install Complete";

?>
