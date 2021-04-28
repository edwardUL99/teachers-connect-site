<!DOCTYPE html>
<html>
   <head>
      <title>Vacancies</title>
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
         generateNavBar(VACANCIES);





         ?>
      <div class="container main-background">
         <div class="d-flex" style ="margin-top: 20px">
            <form class="d-flex" action= "vacancies.php" method = "GET">
               <input class="form-control me-2"
                  type="search"  name ="r" placeholder="Skills - eg.: english, math, science"
                  aria-label="Search">
               <input class="form-control me-2"
                  type="search"  name ="s" placeholder="Search Organisations"
                  aria-label="Search">
               <button class="btn btn" style="background-color:white; margin-right: 10px"
                  type="submit">Search</button>
            </form>
            <a href="vacancies.php"><button class="btn btn"
               style="background-color:white">Refresh</button></a>
         </div>
         <?php
            if(isset($_GET['r'])){
                if($_GET['r'] == ''){


                   if($_GET['s'] != ''){

                       $s = $_GET['s'];
                       $query = mysqli_query($conn, "select * from vacancies where organisation_id = '$s'");
                 while($row = mysqli_fetch_array($query)){
                $vacancy_id = $row['vacancy_id'];
                $job_title = $row['job_title'];

                $description = $row['description'];
                $type = $row['type'];

                $profile_photo = $row['profile_photo'];
                $profile_photo = (!empty($profile_photo)) ? $profile_photo:DEFAULT_ORG_PROFILE_PIC;
                echo '<div class="card" style ="margin-top: 20px; margin-bottom: 20px">
                                 <div class="card-body">
                                     <div class="row">
                                         <div class="col-4">
                                             <img class="rounded-circle" src="'.$profile_photo.'" alt="Organisation Profile Image">
                                         </div>
                                         <div class="col-8">
                                             <a href="vacancy_profile.php?id='.$vacancy_id.'">' . $job_title . '</a><br />

                                             <h5 class="card-title">'. $description .'</h5>
                                             <h5 class="card-title">'. $type .'</h5>


                                         </div>
                                     </div>
                                 </div>
                             </div>';
                }
              }
               }

            }




            if(isset($_GET['r'])){
                if($_GET['r'] != ''){
                $myString = $_GET['r'];
                $myArray = explode(',', $myString);
                $skillstring = '';
               foreach ($myArray as $value){



                 $skillstring = $skillstring . " name = '$value' or";


               }

                 $skillstring = substr($skillstring, 0, -3 );

                 //echo $skillstring;

                 $querystring =   "select distinct vacancies.vacancy_id, organisation_id, job_title, description, type from vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id join vacancy_skills on vacancies.vacancy_id = vacancy_skills.vacancy_id where vacancy_skills.skill_id in (select skill_id from skills where $skillstring)";
                 if(isset($_GET['s'])){
                 $sString = $_GET['s'];
                 if($sString != ''){
                 $querystring =   "select distinct vacancies.vacancy_id, organisation_id, job_title, description, type from vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id join vacancy_skills on vacancies.vacancy_id = vacancy_skills.vacancy_id where vacancies.organisation_id = '$sString' and vacancy_skills.skill_id in (select skill_id from skills where $skillstring)"; }
                 }


                 $query = mysqli_query($conn, $querystring);
                 while($row = mysqli_fetch_array($query)){
                $vacancy_id = $row['vacancy_id'];
                $job_title = $row['job_title'];

                $description = $row['description'];
                $type = $row['type'];

                $profile_photo = $row['profile_photo'];
                $profile_photo = (!empty($profile_photo)) ? $profile_photo:DEFAULT_ORG_PROFILE_PIC;
                echo '<div class="card" style ="margin-top: 20px; margin-bottom: 20px">
                                 <div class="card-body">
                                     <div class="row">
                                         <div class="col-4">
                                             <img class="rounded-circle" src="'.$profile_photo.'" alt="Organisation Profile Image">
                                         </div>
                                         <div class="col-8">
                                             <a href="vacancy_profile.php?id='.$vacancy_id.'">' . $job_title . '</a><br />

                                             <h5 class="card-title">'. $description .'</h5>
                                             <h5 class="card-title">'. $type .'</h5>


                                         </div>
                                     </div>
                                 </div>
                             </div>';
                }
                }}


            if(!isset($_GET['r'])){

                //$q =$_GET['q'];


            $query = mysqli_query($conn, "SELECT * FROM vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id;");
            while($row = mysqli_fetch_array($query)){
                $vacancy_id = $row['vacancy_id'];
                $job_title = $row['job_title'];

                $description = $row['description'];
                $type = $row['type'];

                $profile_photo = $row['profile_photo'];
                $profile_photo = (!empty($profile_photo)) ? $profile_photo:DEFAULT_ORG_PROFILE_PIC;
                 echo '<div class="card" style ="margin-top: 20px; margin-bottom: 20px">
                                 <div class="card-body">
                                     <div class="row">
                                         <div class="col-4">
                                             <img class="rounded-circle" src="'.$profile_photo.'" alt="Organisation Profile Image">
                                         </div>
                                         <div class="col-8">
                                             <a href="vacancy_profile.php?id='.$vacancy_id.'">' . $job_title . '</a><br />

                                             <h5 class="card-title">'. $description .'</h5>
                                             <h5 class="card-title">'. $type .'</h5>


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
