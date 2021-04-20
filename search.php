

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

         if(isset($_GET['q'])){

                $q =$_GET['q'];}

          if(isset($_GET['r'])){

                $r =$_GET['r'];
               }



     echo "<div class='container main-background'>
         <form class='d-flex' action= 'search.php' method = 'GET' style ='margin-top: 20px'>
            <input class='form-control me-2'
               type='search'  name ='q' placeholder='$q'
               aria-label='Search'>
                <input type='hidden' name='r' value='$r' />
            <button class='btn btn' style='background-color:white'
               type='submit'>Search</button>
         </form>";

          ?>

         <?php

            $query = mysqli_query($conn, "select * from teachers where first_name like '$q%' or concat(first_name, \" \", last_name) like '$q%'");

            $rowCount = mysqli_num_rows($query);

            $query2 = mysqli_query($conn, "select * from organisations where name like '$q%'");


             $rowCount2 = mysqli_num_rows($query2);



               echo "<br>";


               echo "<container><a href='search.php?q=$q&r=teacher'>Teachers($rowCount)</a>    <a href='search.php?q=$q&r=organisation'>Organisations($rowCount2)</a>    <a href='search.php?q=$q&r='>Reset</a></container>";


               if($r !="teacher" && $r !="organisation"){



                   if($rowCount >= $rowCount2  && $rowCount > 0){
                       $r = "teacher";}

                   else if($rowCount < $rowCount2){
                       $r = "organisation";}

                    else {

                        echo "<br>";
                        echo "<br>";

                        echo "No results for search '$q'";

                    }








               }









            if($r=="teacher"){
            $query = mysqli_query($conn, "select * from teachers where first_name like '$q%' or concat(first_name, \" \", last_name) like '$q%'");

            $rowCount = mysqli_num_rows($query);



            echo "<br>";
            echo "<br>";

            echo "Found $rowCount teachers for search '$q'";


            while($row = mysqli_fetch_array($query)){
                $first_name = $row['first_name'];
                $last_name = $row['last_name'];
                $username = $row['username'];
                $headline = $row['headline'];
                $profile_photo = $row['profile_photo'];
                $profile_photo = ($profile_photo == null) ? DEFAULT_TEACHER_PROFILE_PIC:$profile_photo;

                 echo '<div class="card" style ="margin-top: 20px; margin-bottom: 20px">
                                 <div class="card-body">
                                     <div class="row">
                                         <div class="col-4">
                                             <img class="card-img-top rounded-circle" src='."\"{$profile_photo}\"".' alt="Card image">
                                         </div>
                                         <div class="col-8">
                                             <a href="teacher_profile.php?username='.$username.'">' . $first_name . ' '. $last_name . '</a><br />

                                             <h5 class="card-title">'. $headline .'</h5>

                                         </div>
                                     </div>
                                 </div>
                             </div>';

            }



            }


            if($r=="organisation"){
            $query = mysqli_query($conn, "select * from organisations where name like '$q%'");


            $rowCount = mysqli_num_rows($query);
            echo "<br>";
            echo "<br>";

            echo "Found $rowCount organisations for search '$q'";

            while($row = mysqli_fetch_array($query)){
                $name = $row['name'];
                $headline = $row['headline'];
                $location = $row['location'];
                $username = $row['username'];
                $profile_photo = $row['profile_photo'];
                $profile_photo = ($profile_photo == null) ? DEFAULT_TEACHER_PROFILE_PIC:$profile_photo;

                 echo '<div class="card" style ="margin-top: 20px; margin-bottom: 20px">
                                 <div class="card-body">
                                     <div class="row">
                                         <div class="col-4">
                                             <img class="card-img-top rounded-circle" src='."\"{$profile_photo}\"".' alt="Card image">
                                         </div>
                                         <div class="col-8">
                                             <a href="organisation_profile.php?username='.$username.'">' . $name .  '</a><br />

                                             <h5 class="card-title">'. $headline .'</h5>

                                         </div>
                                     </div>
                                 </div>
                             </div>';

            }


            }







            ?>
      </div>
      </script>
   </body>
</html>
