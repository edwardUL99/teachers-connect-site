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
      require "teacher.php";
      require "navbar.php";
      require "notifications_utils.php";

      $own_profile = false;
      $teacher_viewer = false;
      $teacher_username = "";
      $following = false; // true if you are following this organisation or not
      $followers = array();

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

      /**
        * Retrieve the number of followers for this organisation
        */
      function loadFollowers() {
        global $conn;
        global $organisation;
        global $followers;

        $sql = "SELECT * FROM followed_organisations JOIN teachers ON teacher_username = username WHERE organisation_id = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("i", $param_org);
          $param_org = $organisation->organisation_id();

          if ($stmt->execute()) {
            $results = $stmt->get_result();

            while ($row = $results->fetch_assoc()) {
              $followers[] = new Teacher($row['teacher_username'], $row['first_name'],
               $row['last_name'], $row['headline'], $row['about'], $row['location'], $row['profile_photo']);
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
        * Display the followers on the profile
        */
      function displayFollowers() {
        global $followers;

        foreach ($followers as $key => $value) {
          $img_src = ($value->profile_photo() == null) ? DEFAULT_TEACHER_PROFILE_PIC:"\"{$value->profile_photo()}\"";
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

      /**
        * Sends the profile viewed notification
        */
      function sendNotification() {
        global $username;
        global $own_profile;

        if (!$own_profile) {
          $sender = $_SESSION[USERNAME];
          $user_type = $_SESSION[USER_TYPE];

          $link = ($user_type == TEACHER) ? "teacher_profile.php?username={$sender}":"organisation_profile.php?username={$sender}";

          $notification = new ViewNotification($sender, $username, false, $link, null);
          addNotification($notification);
        }
      }

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
        if (empty($error_message)) {
          loadFollowers();
          if (empty($error_message)) {
            checkBanned($username);
            checkBlacklist($username);

            if ($user_type != ADMIN && ($banned || $blacklisted)) {
              doError("You cannot view this profile as the user has been banned");
            } else {
              sendNotification();
            }
          }
        }
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
          <img class="img-fluid rounded-circle" src="<?php $photo = $organisation->profile_photo(); echo ($photo == null) ? DEFAULT_ORG_PROFILE_PIC:$photo; ?>" alt="profile-picture">
        </div>
        <div class="col-9">
          <h3><?php echo "{$organisation->name()}"; ?></h3>
          <h4 class="subtitle"><?php $headline = $organisation->headline(); echo ($headline == null) ? "":$headline; ?></h4>
          <h5><?php echo $organisation->location(); ?></h5>
          <p class="about-me-text"><?php $about = $organisation->about(); echo ($about == null) ? "":$about; ?></p>
        </div>
        <?php if ($user_type != ORGANISATION || $own_profile): ?>
        <div class="row mt-2">
          <div class="btn-toolbar">
            <?php echo getPrimaryProfileButton(); ?>
            <?php if ($user_type == ADMIN): ?>
              <?php getBlockButton(); ?>
              <?php getBlacklistButton(); ?>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        </div>
        <?php if ((!$own_profile || $user_type == ADMIN) && $user_type != ORGANISATION): ?>
        <div class="row shadow profile-card">
          <div class="row">
            <div class="col-1">
              <h4 class="underlined-header">People</h4>
            </div>
            <div class="col-4 text-center">
              <p>Highlights of this organisation's following</p>
            </div>
          </div>
          <div class="row">
            <div class="col pl-5">
              <ul>
                <li><span id="followers_number"><?php echo count($followers); ?></span> Followers</li>
              </ul>
            </div>
          </div>
          <div class="row" id="followers_view">
            <?php displayFollowers(); ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="row shadow profile-card">
          <div class="row">
            <h4 class="underlined-header">Vacancies</h4>
          </div>
        </div>

        <?php require "ban_modal.php"; ?>
        <?php require "blacklist_modal.php"; ?>

      <?php endif; ?>
      </div>

    <script type="text/javascript" src="forms.js"></script>
    <script type="text/javascript" src="ajax.js"></script>
    <script>
      const username = <?php echo json_encode($username); ?>;
      const organisation_id = <?php if ($organisation != null) { echo json_encode($organisation->organisation_id()); } ?>;
      const loggedin_username = <?php echo json_encode($_SESSION[USERNAME]); ?>;
      var own_profile = <?php echo json_encode($own_profile); ?>;
      var teacher_viewing = <?php echo json_encode($teacher_viewer); ?>;
      var following = <?php echo json_encode($following); ?>;

      const ajax_progress = document.getElementById('ajax-progress');
      ajax_progress.style.display = "none";
      const profile_header = document.getElementById('profile-header');

      const followButton = document.getElementById('follow-button');
      const followers_number = document.getElementById('followers_number');
      const followers_view = document.getElementById('followers_view');
      sortFollowers();

      const DEFAULT_TEACHER_PROFILE_PIC = <?php echo json_encode(DEFAULT_TEACHER_PROFILE_PIC); ?>;

      var banned = <?php echo json_encode($banned); ?>;
      var blacklisted = <?php echo json_encode($blacklisted); ?>;

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
        * Sort followers by name
        */
      function sortFollowers() {
        var childrenCount = followers_view.childElementCount;
        var children = new Array();

        for (var child of followers_view.children) {
          children.push(child);
        }

        children.sort(function(a, b) {
          return a.id.localeCompare(b.id);
        })

        for (var child of children) {
          followers_view.appendChild(child);
        }
      }

      /**
        * Creates a new follower in the followers_view from the provided data object
        */
      function createNewFollower(data) {
        var card = document.createElement("div");
        card.classList.add("card", "m-2", "align-items-center", "person-card");
        card.id = "follower-" + data['username'];

        var img = document.createElement("img");
        img.src = data['profile_photo'] == null ? DEFAULT_TEACHER_PROFILE_PIC:data['profile_photo'];
        img.classList.add("card-img-top", "rounded-circle");
        card.appendChild(img);

        var card_body = document.createElement("div");
        card_body.classList.add("card-body");
        card.appendChild(card_body);

        var name = document.createElement("h5");
        name.classList.add("card-title");
        name.innerHTML = data['first_name'] + " " + data['last_name'];
        card_body.appendChild(name);

        var headline = document.createElement("p");
        headline.classList.add("card-text");
        headline.innerHTML = data['headline'];
        card_body.appendChild(headline);

        var link = document.createElement("a");
        link.classList.add("btn", "btn-primary");
        link.href = "teacher_profile.php?username=" + data['username'];
        link.innerHTML = "View Profile";
        card_body.appendChild(link);

        var number = parseInt(followers_number.innerHTML);
        followers_number.innerHTML = ++number;

        followers_view.appendChild(card);

        sortFollowers();
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

                      var card = document.getElementById('follower-' + loggedin_username);
                      if (card != null) {
                        var number = parseInt(followers_number.innerHTML);
                        followers_number.innerHTML = --number;

                        card.remove();
                      }
                    } else if (message == "FOLLOWED") {
                      if (button != null)
                        button.innerHTML = "Following";

                      following = true;
                      createNewFollower(responseBody.data);
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

      /**
        * Add return url to the form
        */
      function addReturnURL(form) {
        var returnURL = document.createElement('input');
        returnURL.type = "hidden";
        returnURL.id = "return_url";
        returnURL.name = "return_url";
        returnURL.value = "organisation_profile.php?username=" + username;
        form.appendChild(returnURL);
      }

      /**
        * Ban the user
        */
      function ban() {
        var form = document.forms['ban_user_form'];
        form.action = "ban_user.php";
        form.method = "post";
        addReturnURL(form);

        var valid = true;
        if (!banned)
          valid = validateForm('ban_user_form');

        if (valid) {
          var action = document.createElement("input");
          action.type = "hidden";
          action.id = "action";
          action.name = "action";
          action.value = banned ? "unban":"ban";
          form.appendChild(action);

          var usernameField = document.getElementById('username');
          var adminField = document.getElementById('admin');
          if (banned) {
            usernameField.value = username;
            adminField.value = loggedin_username;
          }

          form.submit();
        }
      }

      /**
        * Blacklist the user
        */
      function blacklist() {
        var form = document.createElement('form');
        form.action = "ban_user.php";
        form.method = "post";
        addReturnURL(form);

        var usernameField = document.createElement('input');
        usernameField.id = "username";
        usernameField.name = "username";
        usernameField.value = username;
        usernameField.type = "hidden";
        form.appendChild(usernameField);

        var admin = document.createElement('input');
        admin.id = "admin";
        admin.name = "admin";
        admin.value = loggedin_username;
        admin.type = "hidden";
        form.appendChild(admin);

        var action = document.createElement("input");
        action.type = "hidden";
        action.id = "action";
        action.name = "action";
        action.value = blacklisted ? "unblacklist":"blacklist";
        form.appendChild(action);


        document.body.appendChild(form);
        form.submit();
      }

    </script>
  </body>
</html>
