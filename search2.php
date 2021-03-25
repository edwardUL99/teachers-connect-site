
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
      
    
   
<form class="d-flex" action= "search2.php" method = "GET" style ="margin-top: 20px">


<input class="form-control me-2"
type="search"  name ="q" placeholder="Search"
aria-label="Search">


<button class="btn btn" style="background-color:white"
type="submit">Search</button>


</form>









   
   
   
   <?php
   if(isset($_GET['q'])){
       
       $q =$_GET['q'];
   
   
   $query = mysqli_query($conn, "select * from teachers where first_name like '%$q%' or concat(first_name, \" \", last_name) like '%$q%'");
   while($row = mysqli_fetch_array($query)){
       $first_name = $row['first_name'];
       $last_name = $row['last_name'];
       $username = $row['username'];
       $headline = $row['headline'];
       
        echo '<div class="card" style ="margin-top: 20px; margin-bottom: 20px">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4">
                                    <img class="card-img-top" src="Face.jpg" alt="Card image" style="width:100%">
                                </div>
                                <div class="col-8">
                                    <a href="teacher_profile.php?username='.$username.'">' . $first_name . ' '. $last_name . '</a><br />
                                    
                                    <h5 class="card-title">'. $headline .'</h5>
                                    
                                </div>
                            </div>
                        </div>
                    </div>';
       
   }}
   ?>
     
     
     
      </div>
      
  
      
    
    </script>
  </body>
</html>

