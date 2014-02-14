          </div>
          </div>         
         <div class="clear">
    </div> 
    
    </div>
  </div>
  
   
  <div id="footerMatrjoska">
    <div id="footer">
        <span id="left">All content copyright &copy; 2010-2012 <a href="http://union.edu">Union College</a>, Schenectady, New York</span>
        <span id="right">Site design by Karel Simek, changes by Jiri Matousek and Steven Stangle</span>
        <div class="clear"></div>
    </div>
   </div> 
  
<div id="helper">
<?      
          if (isset($_SESSION['isConnected']))
          {
            if($_SESSION['permission']=="participant") echo "<a href=\"showall.php\">Home</a>\n";
            else echo "<a href=\"index1.php\">Home</a>\n";
            echo "|<a href=\"logout.php\">Logout</a>|<a href=\"aboutme.php\">My Account</a>\n";
          }else{
            echo "<a href=\"index.php\">Login</a>\n";
          }
?>         
            
            
</div>          
</body>
</html>	