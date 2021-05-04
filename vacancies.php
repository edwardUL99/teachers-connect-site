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
      <?php

        if($user_type=='organisation'){

       echo '<div class="container main-background">
        <div class="row mt-5 shadow card padding-1pcent" id="add_vacancy">
          <h4>Add Vacancy</h4>
          <form id="add_vacancy_form" method = "post">
            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label>Job Title</label>
                  <input type="text" pattern="[A-Za-z\-  ]*" name="job_title" id="job_title" title="Please enter alphabetical characters only" class="form-control" placeholder="Teacher" required>
                </div>
                </div>
        <div class="col-6">
                <div class="form-group">
                  <label>Job Type</label>
                  <select class="form-select" id="type" name="type" >

                  <option value="Full-time">Full-time</option>
                  <option value="Part-time">Part-time</option>

                </select>
                  <div class="form-text">
                    Tell us the hours here
                  </div>
                </div>
              </div>
                </div>
              <div class="row">
            <div class="form-group">
              <label>Description</label>
              <textarea name="description" id="description" class="form-control" rows="5" placeholder="Outline the job role here"></textarea>
              <div class="form-text">
                Enter a detailed piece of information about the position here
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label>Skills</label>
                  <input type="text" name="skills" id="skills" class="form-control" maxlength="64" placeholder="English,Arithmetic,JAVA" >
                  <div class="form-text">
                    Enter relevant skills in a comma-separated (,) list
                  </div>
                </div>



            <div class="row text-end">
              <div class="col">
                <button type="submit" class="btn btn-primary">Create</button>
              </div>
            </div>
          </form>
        </div>
        </div>
        </div>';



         if(isset($_POST['job_title']) && isset($_POST['description']) && isset($_POST['type'])){


                $username = $_SESSION['username'];
                $query8 = mysqli_query($conn, "select * from organisations where username = '$username'");
                while($row = mysqli_fetch_array($query8)){
                $organisation_id = $row['organisation_id'];}

        $job_title = $_POST['job_title'];
        $description = $_POST['description'];
        $type = $_POST['type'];
        $skills = $_POST['skills'];

        $sql = "INSERT INTO vacancies (organisation_id, job_title, description, type)
        VALUES ('".$organisation_id."', '".$job_title."', '".$description."','".$type."')";
        $conn->query($sql);
        $last_id = $conn->insert_id;

        $myArray = explode(',', $skills);

        foreach ($myArray as $value) {

            $query9 = mysqli_query($conn, "select * from skills where name = '$value'");
                while($row = mysqli_fetch_array($query9)){
                $skill_id = $row['skill_id'];}

                if(isset($skill_id)){
                    $sql2 = "INSERT INTO vacancy_skills (vacancy_id, skill_id)
                    VALUES ('".$last_id."', '".$skill_id."')";
                    $conn->query($sql2);
                    unset($skill_id);
                }

                else{
                    $sql3 = "INSERT INTO skills (name)
                    VALUES ('".$value."')";
                    $conn->query($sql3);
                    $last_id2 = $conn->insert_id;
                    $sql4 = "INSERT INTO vacancy_skills (vacancy_id, skill_id)
                    VALUES ('".$last_id."', '".$last_id2."')";
                    $conn->query($sql4);


                }

            }

            $success_message = "Vacancy added successfully!";

                echo "<div class=\"row alert m-auto  mt-2 mb-2 alert-success alert-dismissable fade show\" role=\"alert\">{$success_message}";
                echo "<div class=\"col text-end\">";
                echo "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div></div>";




         }
         echo '</div>';
    }


         if($user_type=='teacher' || $user_type=='organisation' || $user_type =='admin'){

         $query4 = mysqli_query($conn, "select * from organisations");

         if ($user_type == ORGANISATION) {
           echo '<div class="row">';
         } else {
           echo '<div class="container main-background">';
         }

         echo '<div class="d-flex" style ="margin-top: 20px">
         <form class="d-flex" autocomplete="off" action= "vacancies.php" method = "GET">

         <input class="form-control me-2"
         type="search"  name ="r" placeholder="Skills - eg.: english, math, science"
         aria-label="Search">

         <input class="form-control me-2" list="orgs_list"
         type="search"  name ="s" placeholder="Search Organisations"
         aria-label="Search">
         <datalist id="orgs_list">';

         while($row = mysqli_fetch_array($query4)){
          $org_name = $row['name'];
         echo '<option>'.$org_name.'<option>';
         }
         echo '</datalist>

         <button class="btn btn" style="background-color:white; margin-right: 10px"
         type="submit">Search</button>

         </form>
         <a href="vacancies.php"><button class="btn btn"
         style="background-color:white">Refresh</button></a>
         </div>';
         }
         ?>
      <?php
         if($user_type=='teacher' || $user_type=='organisation' || $user_type=='admin') {
         if(isset($_GET['r'])){

              if($_GET['r'] == ''){
                  if($_GET['s'] == ''){

                      echo '<br>';
                      echo '<h3>No results<p style="color:blue"></h3>';
                  }}

             if($_GET['r'] == ''){


                if($_GET['s'] != ''){
                    $s = $_GET['s'];
                    $query6 = mysqli_query($conn, "select * from organisations where name = '$s'");
                      while($row = mysqli_fetch_array($query6)){
                        $s = $row['organisation_id'];
                      }


                    $query = mysqli_query($conn, "select * from vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id where vacancies.organisation_id = '$s' order by posted_at desc");
                    while($row = mysqli_fetch_array($query)){
                      $vacancy_id = $row['vacancy_id'];
                      $organisation_id = $row['organisation_id'];
                      $job_title = $row['job_title'];

                      $description = $row['description'];
                      $type = $row['type'];
                      $org_name = $row['name'];
                      $profile_photo = $row['profile_photo'];
                      $profile_photo = ($profile_photo == null) ? DEFAULT_ORG_PROFILE_PIC:$profile_photo;


                       displayVacancy($row);
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
         $querystring =   "select distinct vacancies.vacancy_id, vacancies.organisation_id, job_title, description, type, profile_photo, posted_at from vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id join vacancy_skills on vacancies.vacancy_id = vacancy_skills.vacancy_id where vacancy_skills.skill_id in (select skill_id from skills where $skillstring) order by posted_at desc";
         if(isset($_GET['s'])){
           $sString = $_GET['s'];
           if($sString != ''){
             $querystring = "select distinct vacancies.vacancy_id, vacancies.organisation_id, job_title, description, type, profile_photo, posted_at from vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id join vacancy_skills on vacancies.vacancy_id = vacancy_skills.vacancy_id where organisations.name = '$sString' and vacancy_skills.skill_id in (select skill_id from skills where $skillstring) order by posted_at desc";
           }


         $query = mysqli_query($conn, $querystring);
         while($row = mysqli_fetch_array($query)){
           $vacancy_id = $row['vacancy_id'];

           $organisation_id = $row['organisation_id'];
           $job_title = $row['job_title'];
          $description = $row['description'];
                      $type = $row['type'];
                      $org_name = $row['name'];
                      $profile_photo = $row['profile_photo'];
                      //echo $profile_photo == null;
                      $profile_photo = ($profile_photo == null) ? DEFAULT_ORG_PROFILE_PIC:$profile_photo;
                       displayVacancy($row);
         }
         }
       }
     }

            if (isset($_GET['organisation'])) {
              $org_id = $_GET['organisation'];

              $sql = "SELECT * FROM vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id WHERE vacancies.organisation_id = ?;";

              if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $param_org);
                $param_org = $org_id;

                if ($stmt->execute()) {
                  $result = $stmt->get_result();

                  while($row = $result->fetch_assoc()){
                      displayVacancy($row);
                  }
                } else {
                  doSQLError($stmt->error);
                }

                $stmt->close();
              } else {
                doSQLError($conn->error);
              }
            } else {

             if(!isset($_GET['r'])){
            $teacherSkills = "";
            $teacherSkillsForRecs = "";

            $sql = "SELECT * FROM teacher_skills JOIN skills on teacher_skills.skill_id = skills.skill_id WHERE username = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_username);
          $username = $_SESSION['username'];
          $param_username = $username;

          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

              while ($row = $result->fetch_assoc()) {
                    $teacherSkills = $teacherSkills . $row['name'] . ', ';
                    $teacherSkillsForRecs = $teacherSkillsForRecs . " name = '".$row['name']."' or";



              }}}}

              $teacherSkills = substr($teacherSkills, 0, -2 );
              $url = "edit_teacher.php?";
              $data = array('username' => $username, 'scroll_to_id' => "add_skills");
              $url = $url . http_build_query($data);
            if($user_type=='teacher'){
             echo '<br><h6>Recommended just for you, based on skills: <p style="color:blue"><i>' . $teacherSkills . '</i></p></h6>
                    <p><a href="'.$url.'">Click here</a> to edit</p>';}

             if($user_type == 'organisation' || $user_type == 'admin'){
             $query = mysqli_query($conn, "SELECT * FROM vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id order by posted_at desc;");
             while($row = mysqli_fetch_array($query)){
                 displayVacancy($row);
             }}


             $teacherSkillsForRecs = substr($teacherSkillsForRecs, 0, -3 );

            if($user_type == 'teacher'){

            $count = 0;

             $queryString =   "select distinct vacancies.vacancy_id, vacancies.organisation_id, job_title, description, type, profile_photo, posted_at from vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id join vacancy_skills on vacancies.vacancy_id = vacancy_skills.vacancy_id where vacancy_skills.skill_id in (select skill_id from skills where $teacherSkillsForRecs) order by posted_at desc";
             $query100 = mysqli_query($conn, $queryString);
             while($row = mysqli_fetch_array($query100)){
                 $count = $count + 1;
                 displayVacancy($row);
             }
            if($count == 0){
             echo '<h6>No results<p style="color:blue"></h6>';

            }

            echo '<h5>And everything else:</h5>';


            if($count > 0){
             $queryString =   "select * from vacancies where vacancy_id not in (select distinct vacancies.vacancy_id from vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id join vacancy_skills on vacancies.vacancy_id = vacancy_skills.vacancy_id where vacancy_skills.skill_id in (select skill_id from skills where $teacherSkillsForRecs) order by posted_at desc)";
             $query100 = mysqli_query($conn, $queryString);
             while($row = mysqli_fetch_array($query100)){
                 displayVacancy($row);
             }}

             else{
                $queryString =   "select * from vacancies order by posted_at desc";
             $query100 = mysqli_query($conn, $queryString);
             while($row = mysqli_fetch_array($query100)){
                 displayVacancy($row);
             }

             }
           }

             }
           }
         }

         function displayVacancy($row) {
           $vacancy_id = $row['vacancy_id'];
           $job_title = $row['job_title'];

           $description = $row['description'];
           $type = $row['type'];
           $posted_at = $row['posted_at'];

           $timestamp = strtotime($posted_at);
           $posted_at = date("H:i", $timestamp);
           $posted_at2 = date("d/m/Y", $timestamp);

           $profile_photo = $row['profile_photo'];
           $profile_photo = (!empty($profile_photo)) ? $profile_photo:DEFAULT_ORG_PROFILE_PIC;
            echo '<div class="card hoverable" style ="margin-top: 20px; margin-bottom: 20px" onclick="window.location.href=\'vacancy_profile.php?id='.$vacancy_id.'\'">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <img class="rounded-circle" src="'.$profile_photo.'" alt="Organisation Profile Image">
                                    </div>
                                    <div class="col-8">
                                        <h4>' . $job_title . '</h4>
                                        <h5 class="card-title">'. $type .'</h5>
                                        <h5 class="card-title">Posted at '. $posted_at .' on the '. $posted_at2 .'</h5>
                                    </div>
                                </div>
                            </div>
                        </div>';


         }
         ?>
      <?php



          ?>
      </div>

      </body>
      </html>
