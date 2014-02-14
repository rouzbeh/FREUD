<?php require("require.php");?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title><? echo $title; ?></title>  
  <meta content="text/html; charset=utf-8" http-equiv="Content-type" />     
  <link rel="stylesheet" href="style.css" type="text/css"> 
  
   <!--[if lte IE 6]>
     <style type="text/css">  
         /* Kill IE 5.5 and 6 bugs*/
         #shadowBottom           { bottom: -9px;  }
         
         /* no min-width in ie6, OMG! */
         #wrapperMatrjoska { width:900px; }
     </style>
  <![endif]-->
  <!--[if IE 8]>
     <style type="text/css">  
         /* Kill IE 8 relative position bug*/
         #helper{ top: 13px;}
     </style>
  <![endif]-->
  
  
  <!--[if lte IE 6]>
  <style type="text/css" media="screen">
    .wrapperShadow          { display: none; }
      #header{
      margin-right: 10px;
      }
     #header,
     #content,
     #footerMatrjoska {
        border-left: 1px solid #aaa;
        border-right: 1px solid #aaa;
     }
     #header{border-top: 1px solid #aaa;}
     #header{border-right: 0px solid #aaa;}
     #footerMatrjoska{border-bottom: 1px solid #aaa;}
          width:expression(((document.compatMode && document.compatMode=='CSS1Compat') ? document.documentElement.clientWidth : document.body.clientWidth) < 866 ? "866px" : "auto");
          }
  </style>
  <![endif]-->
  
<?
  if (!isset($filename))
  {
    $filename="";
  }

  if (strcasecmp($filename, "showall.php")==0 || strcasecmp($filename, "aboutme.php")==0)
  {
?>


<script type="text/javascript" langauge="JavaScript">
<!--
function newWindow(action, eid) 
{
    mywindow=window.open('showinfo.php?action=' + action + '&eid='+eid,'Info','resizable=yes,width=640,height=480');
}
//-->
</script>

<?
  }
  
  if (strcasecmp($filename, "timeslotedit.php")==0 || strcasecmp($filename, "statistics.php")==0 || strcasecmp($filename, "managetimeslots.php")==0)
  {
?>

<script type="text/javascript" langauge="JavaScript"><!--
function y2k(number)    { return (number < 1000) ? number + 1900 : number; }

var today = new Date();
var day   = today.getDate();
var month = today.getMonth();
var year  = y2k(today.getYear());

function padout(number) { return (number < 10) ? '0' + number : number; }

function restart() {
    document.data.edate.value = '' + padout(month - 0 + 1) + '-' + padout(day) + '-' + year;
//    document.data.edate.value = '' + year + '-' + padout(month - 0 + 1) + '-' + padout(day);
    mywindow.close();
}

function newWindow() {
    mywindow=open('cal.htm','myname','resizable=no,width=350,height=270');
    mywindow.location.href = 'cal.htm';
    if (mywindow.opener == null) mywindow.opener = self;
}
//--></script>
<?
  }

  if (strcasecmp($filename, "experimentedit.php")==0)
  {
  
?>

<script type="text/javascript" langauge="JavaScript">
<!--
function newWindow(action, eid) 
{
    mywindow=open('showinfo.php?action=' + action + '&eid='+eid,'Info','resizable=yes,width=640,height=640');
}
//-->
</script>
<?
  }
?>  
  
  
</head>
   
<body onload='document.forms[0].elements[0].focus();'>
  
  <div id="wrapperMatrjoska">
      <div id="shadowTop"    class="wrapperShadow"></div>
      <div id="shadowBottom" class="wrapperShadow"></div> 
      <div id="shadowRight"  class="wrapperShadow"></div>
      <div id="shadowLeft"   class="wrapperShadow"></div> 
      <div id="shadowRightUpperCorner" class="wrapperShadow"></div> 
      <div id="shadowLeftUpperCorner"  class="wrapperShadow"></div>   
      <div id="shadowRightLowerCorner" class="wrapperShadow"></div> 
      <div id="shadowLeftLowerCorner"  class="wrapperShadow"></div> 
  
  <div id="wrapper">    
      <div id="header">       
                      <img src="images/top.jpg"  id="logo" alt="logo" title="logo"/>                                   
      </div>
      
      <div id="content">         
    <div id="textMatrjoska">
      <div id="text">

