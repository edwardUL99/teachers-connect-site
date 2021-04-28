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
      require "error.php";
      require "database.php";
      require "teacher.php";

      $loggedin_username = $_SESSION[USERNAME];
      $user_type = $_SESSION[USER_TYPE];


      echo $teacher_username;


      /**
        * Retrieve the number of followers for this organisation
        */
      function loadFriendRequests() {
        global $conn;

        global $friendRequests;
        $teacher_username = $_SESSION[USERNAME];

        //$sql = "SELECT * FROM connections where destination = '$teacher_username' and status = 'pending';";

        $query = mysqli_query($conn, "SELECT * FROM connections where destination = '$teacher_username' and status = 'pending';");


                while($row = mysqli_fetch_array($query)){


                            $query2 = mysqli_query($conn, "SELECT * FROM teachers where username = '".$row['sender']."';");
                            while($row = mysqli_fetch_array($query2)){
                            $friendRequests[] = new Teacher($row['username'], $row['first_name'],
                            $row['last_name'], $row['headline'], $row['about'], $row['location'], $row['profile_photo']);




                }}

      }

      function loadFriends() {
        global $conn;

        global $friends;
        $teacher_username = $_SESSION[USERNAME];

        //$sql = "SELECT * FROM connections where (destination = '$teacher_username' or destination = '$teacher_username') and status = 'accepted';";

        $query = mysqli_query($conn, "SELECT * FROM connections where (destination = '$teacher_username' or destination = '$teacher_username') and status = 'accepted';");


                while($row = mysqli_fetch_array($query)){

                            $friend = $row['sender'];
                            if($friend == $teacher_username) $friend = $row['destination'];


                            $query2 = mysqli_query($conn, "SELECT * FROM teachers where username = '".$friend."';");
                            while($row = mysqli_fetch_array($query2)){
                            $friends[] = new Teacher($row['username'], $row['first_name'],
                            $row['last_name'], $row['headline'], $row['about'], $row['location'], $row['profile_photo']);




                }}

      }



      function displayRequests() {
        global $friendRequests;

        foreach ($friendRequests as $key => $value) {
          $img_src = ($value->profile_photo() == null) ? DEFAULT_TEACHER_PROFILE_PIC:"{$value->profile_photo()}";
          $name = "{$value->firstName()} {$value->lastName()}";
          $headline = "{$value->headline()}";
          $link = "teacher_profile.php?username={$value->username()}";
          echo "<div class=\"card m-2 align-items-center person-card\" id=\"follower-{$value->username()}\">";
          echo "<img class=\"card-img-top rounded-circle\" src=\"{$img_src}\" alt=\"Profile image\">";
          echo "<div class=\"card-body\">";
          echo "<h5 class=\"card-title\">{$name}</h5>";
          echo "<p class=\"card-text\">{$headline}</p>";
          echo "<a href=\"{$link}\" class=\"btn btn-primary\">View Profile</a>";
          echo "</div></div>";
        }
      }
      function displayFriends() {
        global $friends;

        foreach ($friends as $key => $value) {
          $img_src = ($value->profile_photo() == null) ? DEFAULT_TEACHER_PROFILE_PIC:"{$value->profile_photo()}";
          $name = "{$value->firstName()} {$value->lastName()}";
          $headline = "{$value->headline()}";
          $link = "teacher_profile.php?username={$value->username()}";
          echo "<div class=\"card m-2 align-items-center person-card\" id=\"follower-{$value->username()}\">";
          echo "<img class=\"card-img-top rounded-circle\" src=\"{$img_src}\" alt=\"Profile image\">";
          echo "<div class=\"card-body\">";
          echo "<h5 class=\"card-title\">{$name}</h5>";
          echo "<p class=\"card-text\">{$headline}</p>";
          echo "<a href=\"{$link}\" class=\"btn btn-primary\">View Profile</a>";
          echo "</div></div>";
        }
      }

      loadFriendRequests();
      loadFriends();






     ?>

     <?php
        generateNavBar(CONNECTIONS);



      ?>


    <div class="container main-background">
      <div class="row shadow profile-card">
          <div class="row">
            <div class="col-2">
              <h4 class="underlined-header">Friend Requests</h4>
            </div>
            <div class="col-4 text-center">
              <p>These people want to be your friends!</p>
            </div>
          </div>
          <div class="row">
            <div class="col pl-5">

            </div>
          </div>
          <div class="row" id="followers_view">

          <?php displayRequests(); ?>

          </div>
        </div>

        <div class="row shadow profile-card">
          <div class="row">
            <div class="col-2">
              <h4 class="underlined-header">Friends</h4>
            </div>
            <div class="col-4 text-center">
              <p>These are your friends!</p>
            </div>
          </div>
          <div class="row">
            <div class="col pl-5">

            </div>
          </div>
          <div class="row" id="followers_view">

          <?php displayFriends(); ?>

          </div>
        </div>





        </div>

  </body>
</html>
