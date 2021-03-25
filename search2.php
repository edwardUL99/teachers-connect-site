
<!DOCTYPE html>
<html>
  <head>
    <title>Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link type="text/css" href="css/styles.css" rel="stylesheet">
    <link type="text/css" href="css/feed.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type = "text/javascript">
        function active(){
            var searchBar = document.getElementById('searchBar');
            if(searchBar.value == 'Search...'){
                searchBar.value = ''
                searchBar.placeholder = 'Search...'
            }
            
        }
        function inactive(){
            var searchBar = document.getElementById('searchBar');
            if(searchBar.value == ''){
                searchBar.value = ''
                searchBar.placeholder = 'Search...'
                
                
            }
            
        }
    </script>
  </head>
  <body>
    <?php
      require "database.php";
      require "error.php";
      require "navbar.php";
     
      ?>

     

     <?php
        generateNavBar(SEARCH);
        
      
        
        
       
      ?>
      
      
      
      

      
      <div class="container main-background">
      
     <form action= "search2.php" method = "GET" id ="searchForm" />
         <input type="text" name ="q" id="searchBar" placeholder="" value="Search..." maxlength = "25" autocomplete="off" onMouseDown="active();" onBlur ="inactive();" />
         <input type = "submit" id="searchBtn" value="Search" />
   </form>
   
   
   
   <?php
   if(isset($_GET['q'])){
       
       $q =$_GET['q'];
   
   
   $query = mysqli_query($conn, "select * from teachers where first_name like '%$q%'");
   while($row = mysqli_fetch_array($query)){
       $first_name = $row['first_name'];
       $username = $row['username'];
       
       
        echo '<a href="teacher_profile.php?username='.$username.'">' . $first_name . '</a><br />';
       
   }}
   ?>
     
     
     
      </div>
      
  
      
    
    </script>
  </body>
</html>

