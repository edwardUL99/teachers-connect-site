<!DOCTYPE html>
<html>
  <head>
    <title>Organisation Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link type="text/css" href="css/styles.css" rel="stylesheet">
    <link type="text/css" href="css/feed.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <?php
      require "organisation_variables.php";
      require "navbar.php";

      $own_profile = false;
      $teacher_viewer = false;
      $teacher_username = "";
      $following = false; // true if you are following this organisation or not

      /**
       * Parses the URL for any GET parameters
       */
      function parseURL() {
        global $username;

        if ($_SERVER["REQUEST_METHOD"] == "GET") {
          $parsed_url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
          $params = array();
          parse_str($parsed_url, $params);

          if (isset($params['username'])) {
            $username = $params['username'];
          }
        }
      }

      /**
        * Check the followed status if the user type viewing is teacher
        */
      function checkFollow() {
        global $following;
        global $organisation;
        global $teacher_viewer;
        global $teacher_username;
        global $conn;
        $user_type = $_SESSION[USER_TYPE];

        if ($user_type == TEACHER) {
          $teacher_viewer = true;
          $teacher_username = $_SESSION[USERNAME];

          $sql = "SELECT * FROM followed_organisations WHERE organisation_id = ? AND teacher_username = ?;";

          if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $param_id, $param_username);
            $param_id = $organisation->organisation_id();
            $param_username = $teacher_username;

            if ($stmt->execute()) {
              $stmt->store_result();
              $following = $stmt->num_rows == 1;
            } else {
              doSQLError($stmt->error);
            }
          } else {
            doSQLError($conn->error);
          }
        }
      }

      // TODO send notification of view

      /**
        * Get the button for the primary button on profile header
        */
      function getPrimaryProfileButton() {
        global $own_profile;
        global $teacher_viewer;
        global $following;

        $btn_class = "\"btn btn-primary\"";
        $btn_target = ($own_profile) ? "onclick=\"handleEdit();\"":"onclick=\"handleFollow();\"";
        $btn = "<button class={$btn_class} style=\"margin-right: 1vw;\" id=\"follow-button\" {$btn_target}>";
        if ($own_profile) {
          return "{$btn}Edit</button></a>";
        } else {
          $text = ($following) ? "Following":"Follow";
          return "{$btn}{$text}</button>";
        }
      }

      $loggedin_username = $_SESSION[USERNAME];
      $user_type = $_SESSION[USER_TYPE];

      // parse the URL for get parameters which may include a username to view a different user's profile
      parseURL();

      $own_profile = $user_type == ADMIN || $loggedin_username == $username; // if user is admin treat as own profile. If not, it's our own profile is username matches logged in one

      loadOrganisation($username);
      if (empty($error_message)) {
        checkFollow();
      }
     ?>

     <?php
        generateNavBar(PROFILE);
        $error_occurred = displayError();

        if (!$error_occurred): // only display the profile if an error didn't occur
      ?>
     <div class="container main-background">
       <div class="row">
         <div class="alert alert-info" id="ajax-progress"></div>
       </div>
        <div class="row shadow profile-card padding-1pcent" id="profile-header">
          <div class="row">
            <h4 class="underlined-header">Organisation</h4>
          </div>
          <div class="col-3">
            <img class="img-fluid rounded-circle" src="<?php $photo = $organisation->profile_photo(); echo ($photo == null) ? "images/logo.png":$photo; ?>" alt="profile-picture">
          </div>
          <div class="col-9">
            <h3><?php echo "{$organisation->name()}"; ?></h3>
            <h4 class="subtitle"><?php $headline = $organisation->headline(); echo ($headline == null) ? "":$headline; ?></h4>
            <h5><?php echo $organisation->location(); ?></h5>
            <p class="about-me-text"><?php $about = $organisation->about(); echo ($about == null) ? "":$about; ?></p>
          </div>
          <div class="row mt-2">
            <div class="btn-toolbar">
              <?php echo getPrimaryProfileButton(); ?>
            </div>
          </div>
        </div>
        <!--<div class="row shadow profile-card">
          <div class="row">
            <h4 class="underlined-header">Organisation Statistics</h4>
          </div>
        </div>-->
        <div class="row shadow profile-card">
        <div class="row">
          <h4 class="underlined-header">Vacancies</h4>
        </div>
      </div>

      <?php endif; ?>
    </div>

    <script>
      const username = <?php echo json_encode($username); ?>;
      const organisation_id = <?php if ($organisation != null) { echo json_encode($organisation->organisation_id()); } ?>;
      const loggedin_username = <?php echo json_encode($_SESSION[USERNAME]); ?>;
      var own_profile = <?php echo json_encode($own_profile); ?>;
      var teacher_viewing = <?php echo json_encode($teacher_viewer); ?>;
      var following = <?php echo json_encode($following); ?>;

      var ajax_progress = document.getElementById('ajax-progress');
      ajax_progress.style.display = "none";
      var profile_header = document.getElementById('profile-header');

      var followButton = document.getElementById('follow-button');

      /**
        * Updates the ajax_progress message
        */
      function update_progress(progress_message, display) {
        if (!display) {
          setTimeout(function() {
            ajax_progress.style.display = "none";
            ajax_progress.innerHTML = "";
            ajax_progress.style.marginTop = "0%";
            ajax_progress.style.marginBottom = "0%";
            profile_header.style.marginTop = "2%";}, 1000);
        } else {
          ajax_progress.style.display = "block";
          ajax_progress.innerHTML = progress_message;
          ajax_progress.style.marginTop = "2%";
          ajax_progress.style.marginBottom = "2%";
          profile_header.style.marginTop = "0%";
        }
      }

      /**
        * Get the AJAX object
        */
      function getAJAX() {
        var ajaxRequest;

        try {
          ajaxRequest = new XMLHttpRequest();
        } catch (e) {
          try {
            ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
          } catch (e) {
            try {
              ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {
              alert("An error occurred. Your browser may not be supported by this website");
              return null;
            }
          }
        }

        return ajaxRequest;
      }

      /**
        * Handles a follow request
        */
      function handleFollow() {
        if (teacher_viewing) {
          var ajaxRequest = getAJAX();

          if (ajaxRequest != null) {
            ajaxRequest.onreadystatechange = function() {
              if (ajaxRequest.readyState == 4) {
                var response = ajaxRequest.response;
                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;
                  var button = document.getElementById('follow-button');

                  if (success) {
                    if (message == "REMOVED") {
                      if (button != null)
                        button.innerHTML = "Follow";
                      following = false;
                    } else if (message == "FOLLOWED") {
                      if (button != null)
                        button.innerHTML = "Following";

                      following = true;
                    }

                    update_progress("", false);
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(e);
                }
              }
            }

            var url = "profile-action-ajax.php";
            var data = {};
            data['action'] = "follow";
            data['sender'] = loggedin_username;
            data['destination'] = organisation_id;

            if (following) {
              update_progress("Unfollowing organisation", true);
              data['action_param'] = "remove";
            } else {
              update_progress("Following organisation", true);
              data['action_param'] = "add";
            }

            ajaxRequest.open("POST", url, true);
            var json = JSON.stringify(data);
            ajaxRequest.send(json);
          }
        }
      }

      /**
        * Handles the edit button being pressed
        */
      function handleEdit() {
        window.location.href = `edit_organisation.php?username=${username}`;
      }

    </script>
  </body>
</html>
