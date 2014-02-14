<?php
/*
Library for conversion European format of date and 
time to American format of date and time and reverse.
This is necessary to do, because Mysql uses European format 
and we need to display it in American format.

*/

class ctime
{
  var $hour;
  var $minute;
  var $daypart;
}

//
// 16:30:00 to 4:30 pm, returns object 
//
function time24to12e($time)
{
  $ct=new ctime;
  $ct->hour=(substr($time, 0, 2))%12;
  $ct->minute=substr($time, 3, 2);

  if (substr($time, 0, 2)>12)
  { 
    $ct->daypart="pm";
  }else{
    $ct->daypart="am";
  }
  return $ct;
}

//
// 16:30:00 to 4:30 pm, returns string
//
function time24to12($time)
{
  $hour1=(substr($time, 0, 2));
  if ($hour1==0 || $hour1==12)
  {
    $hour=12;
  } else{
    $hour=$hour1%12;
  }
  if ($hour==0) $hour=12;
  $minute=substr($time, 3, 2);

  if (substr($time, 0, 2)>=12)
  { 
    $daypart="pm";
  }else{
    $daypart="am";
  }
  return $hour.":".$minute." ".$daypart;
}

//
// 4:30 pm to 16:30:00
//
function time12to24($hour, $minute, $daypart)
{
  if (strcasecmp($daypart, "am")==0)
  {
    if ($hour==12) $hour=0;
    return $hour.":".$minute.":00";
  }

  if (strcasecmp($daypart, "pm")==0)
  {
    if ($hour!=12) $hour=$hour+12;
    return $hour.":".$minute.":00";
  }
}

//
// 2007-02-20 -> 02-20-2007
//
function transformDateYearLast($date)
{
  $date1=substr($date, 5, 5)."-".substr($date, 0, 4);
  return $date1;
} 

//
// 2007-02-20 -> 02-20-2007
//
function transformDateYearFirst($date)
{
   $pieces = explode("-", $date);
  if(sizeof($pieces)!=3)
   $pieces = explode("/", $date);
  if(sizeof($pieces)!=3)
      return "0000-00-00";
  else    
    $date1 = $pieces[2].'-'.$pieces[0].'-'.$pieces[1];
    //$date1=substr($date, 6, 4)."-".substr($date, 0, 5);
    return $date1;
  
} 

//
// given date in Euro format, returns day of the week (MO...SU)
//
function dayofweek($date) //0 is monday
{
  $fyear=substr($date, 0, 4);
  $fmonth=substr($date, 5, 2);
  $fday=substr($date, 8, 2);

  $dow = (((mktime ( 0, 0, 0, $fmonth, $fday, $fyear) - mktime ( 0, 0, 0, 7, 17, 2006))/(60*60*24))+700000) % 7;
  switch ($dow)
  {
    case 0: {return "MO"; break;}
    case 1: {return "TU"; break;}
    case 2: {return "WE"; break;}
    case 3: {return "TH"; break;}
    case 4: {return "FR"; break;}
    case 5: {return "SA"; break;}
    case 6: {return "SU"; break;}
  }
}

?>
