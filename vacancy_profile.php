<!DOCTYPE html>
<html>
  <head>
    <title>Vacancy Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link type="text/css" href="css/styles.css" rel="stylesheet">
    <link type="text/css" href="css/feed.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <?php

      require "navbar.php";




      /**
       * Parses the URL for any GET parameters
       */
        function parseURL() {
        global $id;

        if ($_SERVER["REQUEST_METHOD"] == "GET") {
          $parsed_url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
          $params = array();
          parse_str($parsed_url, $params);

          if (isset($params['id'])) {
            $id = $params['id'];
          }
        }
      }

      $loggedin_username = $_SESSION[USERNAME];
      $user_type = $_SESSION[USER_TYPE];

     parseURL();


     ?>

     <?php
        generateNavBar(VACANCIES);


          $query = mysqli_query($conn, "SELECT * FROM vacancies JOIN organisations ON vacancies.organisation_id = organisations.organisation_id WHERE vacancy_id = '$id'");
          while($row = mysqli_fetch_array($query)){
            $organisation_id = $row['organisation_id'];
            $job_title = $row['job_title'];

            $description = $row['description'];
            $type = $row['type'];

            $org_name = $row['name'];
            $profile_photo = $row['profile_photo'];
            $profile_photo = ($profile_photo == null) ? DEFAULT_ORG_PROFILE_PIC:$profile_photo;
            $org_username = $row['username'];
          }
      ?>
     <div class="container main-background">

       <div class="row shadow profile-card padding-1pcent" id="profile-header">
        <div class="row">
          <h4 class="underlined-header">Vacancy</h4>
        </div>
        <div class="col-3">
          <img class="img-fluid rounded-circle" src=<?php echo "'".$profile_photo."'";?> alt="profile-picture">
        </div>
        <div class="col-9">
          <a href="organisation_profile.php?username=<?php echo $org_username; ?>"><h3><?php echo $org_name; ?></h3></a>
          <h4 class="subtitle"><?php echo $job_title;?></h4>
          <h5><?php echo $type; ?></h5>
          <p class="about-me-text"><?php echo $description; ?></p>
        </div>

        </div>





      </div>
  </body>
</html>
