<!DOCTYPE html>
<html>
  <head>
    <title>Teacher Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link type="text/css" href="css/styles.css" rel="stylesheet">
    <link type="text/css" href="css/feed.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <?php
      require "database.php";
      require "error.php";
      require "navbar.php";
      require "teacher.php";
      require "organisation.php";

      $teacher = null;
      $current_organisation = null;
      $own_profile = false;
      $connected = null;
      $blocked_user = false; // true if we have blocked the viewed user
      $qualification = null; // this is the most recent qualification

      /**
        * These variables hold the sender and receiver of the connection between the teacher and viewer if any
        */
      $connection_sender = "";
      $connection_receiver = "";
      $connection_pending = "";

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
        * Loads the teacher from the database into a Teacher Object
        */
      function loadTeacher() {
        global $teacher;
        global $username;
        global $conn;

        $sql = "SELECT * FROM teachers WHERE username = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_user);
          $param_user = $username;

          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
              while ($row = $result->fetch_assoc()) {
                $teacher = new Teacher($row['username'], $row['first_name'],
                $row['last_name'], $row['headline'], $row['about'], $row['location'], $row['profile_photo']);
              }

              loadCurrentOrganisation();
            } else {
              doError("No teacher found for username {$username}!");
            }
          } else {
            doSQLError($stmt->error);
          }

          $stmt->close();
        } else {
          doSQLError($conn->error);
        }
      }

      /**
        * Load the current organisation of this teacher if any
        */
      function loadCurrentOrganisation() {
        global $username;
        global $current_organisation;
        global $conn;

        $sql = "SELECT * FROM organisations WHERE organisation_id IN (SELECT organisation_id FROM organisation_members WHERE teacher_username = ?);";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_user);
          $param_user = $username;

          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
              while ($row = $result->fetch_assoc()) {
                $current_organisation = new Organisation($row['organisation_id'], $row['username'],
                $row['name'], $row['headline'], $row['about'], $row['location'], $row['profile_photo']);
              }
            }
          } else {
            doSQLError($stmt->error);
          }

          $stmt->close();
        } else {
          doSQLError($conn->error);
        }
      }

      /**
        * This function loads the teacher's most recent qualification if any
        */
      function loadRecentQualification() {
        // TODO load recent qualification if any here
      }

      /**
        * Check if you are connected with the current user
        */
      function checkConnection() {
        global $connected;

        if (!isset($connected)) {
          global $username;
          global $conn;
          global $connection_sender;
          global $connection_receiver;
          global $connection_pending;

          $loggedin_username = $_SESSION[USERNAME];

          $sql = "SELECT * FROM connections WHERE (destination = ? AND sender = ?) OR (destination = ? AND sender = ?) LIMIT 1;";

          if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $param_dest1, $param_send1, $param_dest2, $param_send2);
            $param_dest1 = $loggedin_username;
            $param_send1 = $username;
            $param_dest2 = $username;
            $param_send2 = $loggedin_username;

            if ($stmt->execute()) {
              $result = $stmt->get_result();
              if ($result->num_rows == 1) {
                while ($row = $result->fetch_assoc()) {
                  $connection_sender = $row['sender'];
                  $connection_receiver = $row['destination'];
                  $connection_pending = $row['status'] == "pending";

                  $connected = true;
                }
              } else {
                $connection_sender = $loggedin_username;
                $connection_receiver = $username;
                $connected = false;
              }
            } else {
              doSQLError($stmt->error);
              $connected = false;
            }

            $stmt->close();
          } else {
            doSQLError($conn->error);
            $connected = false;
          }
        }

        return $connected;
      }

      /**
        * Determine if connection is pending and the logged in user is the destination
        */
      function displayAcceptConnection() {
        global $connection_pending;
        global $connection_receiver;

        $loggedin_username = $_SESSION[USERNAME];
        return $connection_pending && $loggedin_username == $connection_receiver;
      }

      /**
        * Determine if connection is pending and you sent the connection
        */
      function displayConnectionRequested() {
        global $connection_pending;
        global $connection_sender;

        $loggedin_username = $_SESSION[USERNAME];
        return $connection_pending && $loggedin_username == $connection_sender;
      }

      /**
        * Gets the URL for the connection_handler
        */
      function getHandlerURL($connected) {
        global $connection_sender;
        global $connection_receiver;
        global $connection_pending;

        $action = $connected ? REMOVE:ADD;

        if (displayAcceptConnection()) {
          $action = ACCEPT;
        }

        $data = array(ACTION => CONNECT, SENDER => $connection_sender, DESTINATION => $connection_receiver, ACTION_PARAM => $action, RETURN_URL => $_SERVER["REQUEST_URI"]);
        $url = http_build_query($data, '', '&amp;');
        return "profile-action.php?{$url}";
      }

      /**
        * Gets the URL for blocking this user
        */
      function getBlockURL() {
          global $own_profile;

          if (!$own_profile) {
            global $username;
            global $blocked_user;

            $loggedin_username = $_SESSION[USERNAME];
            $action = ($blocked_user) ? REMOVE:ADD;


            $data = array(ACTION => BLOCK, SENDER => $loggedin_username, DESTINATION => $username, ACTION_PARAM => $action, RETURN_URL => ($blocked_user) ? $_SERVER['REQUEST_URI']:"teacher_profile.php");
            $url = http_build_query($data, '', '&amp;');
            return "profile-action.php?{$url}";
          }

          return "#";
      }

      /**
        * If this profile is not the user's own profile, it checks if they have been blocked.
        * Returns true if they can be viewed, false if not
        */
      function canView() {
        global $own_profile;

        if (!$own_profile) {
          global $conn;
          global $username;

          $loggedin_username = $_SESSION[USERNAME];

          $sql = "SELECT * FROM blocked_teachers WHERE (blocked_teacher = ? AND blocker = ?) OR (blocked_teacher = ? AND blocker = ?);";

          if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $param_blocked1, $param_blocker1, $param_blocked2, $param_blocker2);
            $param_blocked1 = $loggedin_username;
            $param_blocker1 = $username;
            $param_blocked2 = $username;
            $param_blocker2 = $loggedin_username;

            if ($stmt->execute()) {
              $result = $stmt->get_result();

              if ($result->num_rows == 1) {
                while ($row = $result->fetch_assoc()) {
                  $blocked = $row['blocked_teacher'];
                  $blocker = $row['blocker'];

                  if ($blocked == $loggedin_username) {
                    doError("You have been blocked by {$row['blocker']}. You cannot view this profile");
                    $stmt->close();
                    return false;
                  } else if ($blocked = $username && $blocker == $loggedin_username) {
                    global $blocked_user;
                    $blocked_user = true;

                    $stmt->close();
                    return true;
                  }
                }
              }

              $stmt->close();

              return true;
            } else {
              doSQLError($stmt->error);
            }

            $stmt->close();
          } else {
            doSQLError($conn->error);
          }

          return false;
        } else {
          return true;
        }
      }

      // TODO send notification of view

      /**
        * Get the button for the primary button on profile header
        */
      function getPrimaryProfileButton() {
        global $own_profile;

        $btn_class = "\"btn btn-primary\"";
        $btn = "<button class={$btn_class} style=\"margin-right: 1vw;\" id=\"connect-button\" onclick=\"handleConnection();\">";
        if ($own_profile) {
          return "{$btn}Edit</button></a>";
        } else {
          $connected = checkConnection();
          $text = ($connected) ? "Connected":"Connect";

          if (displayAcceptConnection()) {
            $text = "Accept Connection";
          } else if (displayConnectionRequested()) {
            $text = "Connection Requested";
          }

          $url = getHandlerURL($connected);

          if (isset($url)) {
            return "{$btn}{$text}</button>";
          } else {
            return "{$btn}{$text}</button>";
          }
        }
      }

      if (isset($_SESSION[LOGGED_IN]) && $_SESSION[LOGGED_IN] == true) {
        if (!isset($_SESSION[USERNAME]) || !isset($_SESSION[USER_TYPE])) {
          goToLogin();
        }

        $loggedin_username = $_SESSION[USERNAME];
        $user_type = $_SESSION[USER_TYPE];

        // parse the URL for get parameters which may include a username to view a different user's profile
        parseURL();

        $own_profile = $user_type == ADMIN || $loggedin_username == $username; // if user is admin treat as own profile. If not, it's our own profile is username matches logged in one

        if (canView()) {
          loadTeacher();
          if (empty($error_message)) {
            loadRecentQualification();
          }
        }
      } else {
        goToLogin();
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
            <h4 class="underlined-header">Teacher</h4>
          </div>
          <div class="col-3">
            <img class="img-fluid rounded-circle" src="<?php $photo = $teacher->profile_photo(); echo ($photo == null) ? "":$photo; ?>" alt="profile-picture">
          </div>
          <div class="col-9">
            <h3><?php echo "{$teacher->firstName()} {$teacher->lastName()}"; ?></h3>
            <h4 class="subtitle"><?php $headline = $teacher->headline(); echo ($headline == null) ? "":$headline; ?></h4>
            <p class="about-me-text"><?php $about = $teacher->about(); echo ($about == null) ? "":$about; ?></p>
          </div>
          <?php
            if (isset($current_organisation)):
          ?>
          <div class="row text-align-center">
            <div class="col-3 current-organisation d-flex align-items-center">
              <h5><?php echo $current_organisation->name(); ?></h5>
            </div>
            <div class="col-3 d-flex align-items-center">
              <img class="img-fluid rounded-circle current-organisation-photo" src="<?php $photo = ($current_organisation != null) ? $current_organisation->profile_photo():null; echo ($photo == null) ? "":$photo; ?>" alt="organisation-photo">
            </div>
          </div>
        <?php endif; ?>
        <div class="row">
          <div class="btn-toolbar">
            <?php if ($own_profile || !$blocked_user): ?>
              <?php echo getPrimaryProfileButton(); ?>
            <?php endif; ?>
            <?php if (!$own_profile): ?>
            <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                More
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <button class="btn btn-light w-100" id="block-button" onclick="handleBlock();"><?php echo ($blocked_user) ? "Unblock":"Block"; ?></a>
              </div>
            </div>
          <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col shadow profile-card right-margin-1pcent">
          <div class="row">
            <h4 class="underlined-header">Most Recent Education</h4>
          </div>
          <?php
            if (isset($qualification)) {
              echo "<div class=\"row\">";
              // TODO education stuff here
              echo "</div>";
            }
          ?>
        </div>
        <div class="col shadow profile-card">
          <div class="row">
            <h4 class="underlined-header">Most Recent Employment</h4>
          </div>
        </div>
      </div>
      <div class="row shadow profile-card">
        <div class="row">
          <h4 class="underlined-header">Posts</h4>
        </div>
      </div>

      <?php endif; ?>
    </div>

    <script>
      const username = <?php echo json_encode($username); ?>;
      const loggedin_username = <?php echo json_encode($_SESSION[USERNAME]); ?>;
      var own_profile = <?php echo json_encode($own_profile); ?>;
      var blocked_user = <?php echo json_encode($blocked_user); ?>;
      var connected = <?php echo json_encode(checkConnection()); ?>;
      var connection_pending = <?php echo json_encode($connection_pending); ?>;
      const connection_sender = <?php echo json_encode($connection_sender); ?>;
      const connection_receiver = <?php echo json_encode($connection_receiver); ?>;
      const server_uri = <?php echo json_encode($_SERVER['REQUEST_URI']); ?>;
      var accept_connection = <?php echo json_encode(displayAcceptConnection()); ?>;
      var request_sent = <?php echo json_encode(displayConnectionRequested()); ?>;

      var ajax_progress = document.getElementById('ajax-progress');
      ajax_progress.style.display = "none";
      var profile_header = document.getElementById('profile-header');

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
        * Handle conection response. Pass in null if you don't want callback
        */
      function handleConnectionResponse(responseBody, callback) {
        var success = responseBody.success;
        var message = responseBody.message;
        var button = document.getElementById('connect-button');

        if (success) {
          if (message == "REMOVED") {
            if (button != null)
              button.innerHTML = "Connect";
            connected = false;
            connection_pending = false;
            request_sent = false;
          } else if (message == "PENDING") {
            if (button != null)
              button.innerHTML = "Connection Requested";
            connected = true;
            connection_pending = true;
            request_sent = true;
          } else if (message == "ACCEPTED") {
            if (button != null)
              button.innerHTML = "Connected";
            connected = true;
            connection_pending = false;
            request_sent = false;
          }

          if (callback != null) {
            callback();
          } else {
            update_progress("", false);
          }
        } else {
          alert(message);
          connected = false;
          connection_pending = false;
        }
      }

      /**
        * Get the url to remove a connection
        */
      function getRemoveConnectionURL() {
        return `profile-action-ajax.php?action=connect&sender=${connection_sender}&destination=${connection_receiver}&action_param=remove`;
      }

      /**
        * Handles connection press
        */
      function handleConnection() {
        var ajaxRequest = getAJAX();

        if (ajaxRequest != null) {
          ajaxRequest.onreadystatechange = function() {
            if (ajaxRequest.readyState == 4) {
              var response = ajaxRequest.response;
              try {
                var responseBody = JSON.parse(response);
                handleConnectionResponse(responseBody, null);
              } catch (e) {
                alert(response);
              }
            }
          }

          var url = null;

          if (!connected) {
            update_progress("Sending connection request", true);
            url = `profile-action-ajax.php?action=connect&sender=${connection_sender}&destination=${connection_receiver}&action_param=add`;
          } else {
            if (connection_pending) {
              if (accept_connection) {
                update_progress("Accepting connection request", true);
                url = `profile-action-ajax.php?action=connect&sender=${connection_sender}&destination=${connection_receiver}&action_param=accept`;
              } else if (request_sent) {
                update_progress("Removing connection request", true);
                url = getRemoveConnectionURL();
              }
            } else {
              update_progress("Removing connection", true);
              url = getRemoveConnectionURL();
            }
          }

          if (url != null) {
            ajaxRequest.open("GET", url, true);
            ajaxRequest.send(null);
          } else {
            console.warn("Connection request URL is null, may be an error");
          }
        }
      }

      /**
        * Callback for blocking the user
        */
      function blockCallback() {
        var ajaxRequest = getAJAX();

        if (ajaxRequest != null) {
          ajaxRequest.onreadystatechange = function() {
            if (ajaxRequest.readyState == 4) {
              var response = ajaxRequest.response;
              try {
                var responseBody = JSON.parse(response);
                var success = responseBody.success;
                var message = responseBody.message;
                var button = document.getElementById('block-button');

                if (success) {
                  if (message == "REMOVED") {
                    button.innerHTML = "Block";
                    blocked_user = false;
                    window.location.reload();
                  } else if (message == "BLOCKED") {
                    button.innerHTML = "Blocked";
                    window.location.href = "teacher_profile.php";
                  }

                  update_progress("", false);
                } else {
                  alert(message);
                }
              } catch (e) {
                alert(response);
              }
            }
          }

          var url = null;

          if (!own_profile) {
            if (!blocked_user) {
              url = `profile-action-ajax.php?action=block&sender=${loggedin_username}&destination=${username}&action_param=add`;
            } else {
              url = `profile-action-ajax.php?action=block&sender=${loggedin_username}&destination=${username}&action_param=remove`;
            }

            if (url != null) {
              ajaxRequest.open("GET", url, true);
              ajaxRequest.send(null);
            } else {
              console.warn("Block URL is null, may be an error");
            }
          }
        }
      }

      /**
        * Handle a block request
        */
      function handleBlock() {
        var ajaxRequest = getAJAX();

        if (ajaxRequest != null) {
          ajaxRequest.onreadystatechange = function() {
            if (ajaxRequest.readyState == 4) {
              var response = ajaxRequest.response;
              try {
                var responseBody = JSON.parse(response);
                handleConnectionResponse(responseBody, blockCallback); // we have removed any connection between these users, so we can now block them
              } catch (e) {
                alert(response);
              }
            }
          }

          var url = null;

          if (!own_profile) {

            if (!blocked_user) {
              update_progress("Blocking user", true);
              url = getRemoveConnectionURL();

              if (url != null) {
                ajaxRequest.open("GET", url, true);
                ajaxRequest.send(null);
              } else {
                console.warn("Connection request URL is null, may be an error");
              }
            } else {
              update_progress("Unblocking user", true);
              blockCallback();
            }
          }
        }
      }

    </script>
  </body>
</html>
