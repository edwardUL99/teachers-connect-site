<!DOCTYPE html>
<html>
  <head>
    <title>Administration</title>
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

      define("BAN", 1);
      define("BLACKLIST", 2);

      /**
        * This class represents a banned user containing a profile photo,
        * name and headline
        */
      class BannedUser {
        /**
          * The profile photo for the user
          */
        public $profile_photo;
        /**
          * The user's username
          */
        public $username;
        /**
          * The name of the user
          */
        public $name;
        /**
          * The reason the user is banned
          */
        public $reason;
        /**
          * The type of the user
          */
        public $type;
        /**
          * Username of the admin who banned them
          */
        public $banned_by;
        /**
          * The date-time they are banned from
          */
        public $banned_from;
        /**
          * The date and time they are banned until
          */
        public $banned_to;

        /**
          * Construct the banned user
          */
        function __construct($profile_photo, $username, $name, $type) {
          $this->profile_photo = $profile_photo;
          $this->username = $username;
          $this->name = $name;
          $this->reason = null;
          $this->type = $type;
          $this->banned_by = null;
          $this->banned_from = null;
          $this->banned_to = null;
        }
      }

      $banned_users = array();
      $blacklisted_emails = array();

      $ban_error_message = "";
      $blacklist_error_message = "";

      /**
        * Retrieve a BannedUser object
        */
      function getTeacher($username) {
        global $conn;

        $sql = "SELECT * FROM teachers WHERE username = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_username);
          $param_username = $username;

          $type = null;

          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
              while ($row = $result->fetch_assoc()) {
                $name = "".$row['first_name']." ".$row['last_name'];
                return new BannedUser($row['profile_photo'], $username, $name, "Teacher");
              }
            }
          } else {
            doSQLError($stmt->error);
          }

          $stmt->close();
          return $type;
        } else {
          doSQLError($conn->error);
        }
      }

      /**
        * Retrieve a BannedUser object
        */
      function getOrganisation($username) {
        global $conn;

        $sql = "SELECT * FROM teachers WHERE username = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_username);
          $param_username = $username;

          $type = null;

          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
              while ($row = $result->fetch_assoc()) {
                return new BannedUser($row['profile_photo'], $username, $row['name'], "Organisation");
              }
            }
          } else {
            doSQLError($stmt->error);
          }

          $stmt->close();
          return $type;
        } else {
          doSQLError($conn->error);
        }
      }

      /**
        * Get the type of the user
        */
      function getUserType($username) {
        global $conn;

        $sql = "SELECT type FROM accounts WHERE username = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_username);
          $param_username = $username;

          $type = null;

          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
              while ($row = $result->fetch_assoc()) {
                $type = $row['type'];
              }
            }
          } else {
            doSQLError($stmt->error);
          }

          $stmt->close();
          return $type;
        } else {
          doSQLError($conn->error);
        }
      }

      /**
        * Load the banned users
        */
      function loadBannedUsers() {
        global $banned_users;
        global $conn;

        $sql = "SELECT * FROM banned_users;";

        if ($stmt = $conn->prepare($sql)) {
          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $username = $row['username'];
                $user_type = getUserType($username);

                $banned_user = null;
                if ($user_type == TEACHER) {
                  $banned_user = getTeacher($username);
                } else if ($user_type == ORGANISATION) {
                  $banned_user = getOrganisation($username);
                }

                if ($banned_user != null) {
                  $date_from = $row['date_from'];
                  $date_from = strtotime($date_from);
                  $date_from = date('d/m/Y H:i', $date_from);

                  $date_to = $row['date_to'];
                  $date_to = strtotime($date_to);

                  if (time() < $date_to) {
                    $date_to = date('d/m/Y H:i', $date_to);

                    $banned_user->banned_from = $date_from;
                    $banned_user->banned_to = $date_to;
                    $banned_user->banned_by = $row['banned_by'];
                    $banned_user->reason = $row['reason'];
                    $banned_users[$username] = $banned_user;
                  }
                }
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
        * Build the data object for unbanning the user
        */
      function getUnbanQuery($username) {
        $loggedin_username = $_SESSION[USERNAME];
        return array('action' => "unban", 'username' => $username, 'admin' => $loggedin_username, 'return_url' => "administration.php");
      }

      /**
        * Displays banned users by printing out each in a formatted card
        */
      function displayBannedUsers() {
        global $banned_users;

        foreach ($banned_users as $key => $value) {
          $type = $value->type;
          $img_src = ($value->profile_photo == null) ? (($type == TEACHER) ? DEFAULT_TEACHER_PROFILE_PIC:DEFAULT_ORG_PROFILE_PIC):"\"{$value->profile_photo}\"";
          $name = $value->name;
          $reason = $value->reason;
          $link = "ban_user.php?".http_build_query(getUnbanQuery($value->username), '', '&amp;');
          echo "<div class=\"card m-2 align-items-center person-card-banned\">";
          echo "<img class=\"card-img-top rounded-circle\" src=\"{$img_src}\" alt=\"Profile image\">";
          echo "<div class=\"card-body\">";
          echo "<div class=\"row\">";
          echo "<h5 class=\"card-title\">{$name}</h5>";
          echo "<h6 class=\"subtitle\">{$type}</h5>";
          echo "<p class=\"card-text\">{$reason}</p>";
          echo "<p class=\"card-text\">From: {$value->banned_from}</p>";
          echo "<p class=\"card-text\">To: {$value->banned_to}</p>";
          echo "</div>";
          echo "<a href=\"{$link}\" class=\"btn btn-danger\">Unban</a>";
          echo "</div></div>";
        }
      }

      /**
        * Load the blacklisted emails
        */
      function loadBlacklistedEmails() {
        global $blacklisted_emails;
        global $conn;

        $sql = "SELECT * FROM email_blacklist;";

        if ($stmt = $conn->prepare($sql)) {
          if ($stmt->execute()) {
            $results = $stmt->get_result();

            if ($results->num_rows > 0) {
              while ($row = $results->fetch_assoc()) {
                $blacklisted_emails[] = $row['email'];
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
        * Get the query string to unblacklist the email
        */
      function getUnblacklistQuery($email) {
        $loggedin_username = $_SESSION[USERNAME];
        return array('action' => 'unblacklist', 'email' => $email, 'admin' => $loggedin_username, 'return_url' => "administration.php");
      }

      /**
        * Displays emails that have been blacklisted
        */
      function displayBlacklistedEmails() {
        global $blacklisted_emails;

        foreach ($blacklisted_emails as $key => $value) {
          $link = "ban_user.php?".http_build_query(getUnblacklistQuery($value), '', '&amp;');
          echo "<div class=\"row mt-2\">";
          echo "<div class=\"col align-self-start\"><h5>{$value}</h5></div>";
          echo "<div class=\"col align-self-end\"><a class=\"btn btn-dark\" href=\"{$link}\">Unblacklist</a></div>";
          echo "</div>";
        }
      }

      /**
        * Ban a user entered in the form
        */
      function banUser() {
        global $banned_users;
        global $ban_error_message;

        $username = (isset($_POST['username'])) ? $_POST['username']:null;

        if ($username == null) {
          die("You need to provide a username to ban");
        }

        if (isset($banned_users[$username])) {
          $ban_error_message = "That user is already banned";
          return;
        }

        $reason = (isset($_POST['reason'])) ? $_POST['reason']:null;

        if ($reason == null) {
          die("You need to provide a reason to ban");
        }

        $date_to = (isset($_POST['date_to'])) ? $_POST['date_to']:null;

        if ($date_to == null) {
          die("You need to provide a date until which the user will be banned");
        }

        $time_to = (isset($_POST['time_to'])) ? $_POST['time_to']:null;

        if ($reason == null) {
          die("You need to provide a time until which the user will be banned on the provided date");
        }

        $loggedin_username = $_SESSION[USERNAME];
        $data = array('action' => 'ban', 'username' => $username,
        'admin' => $loggedin_username, 'reason' => $reason, 'date_to' => $date_to, 'time_to' => $time_to, 'return_url' => "administration.php");

        $url = "ban_user.php?".http_build_query($data, '', '&amp;');
        header("Location: {$url}");
        exit;
      }

      /**
        * Blacklist a user enteered in the form
        */
      function blacklistUser() {
        global $blacklisted_emails;
        global $blacklist_error_message;

        $email = (isset($_POST['email'])) ? $_POST['email']:null;

        if ($email == null) {
          die("You need to provide an email address to blacklist");
        }

        if (in_array($email, $blacklisted_emails)) {
          $blacklist_error_message = "That e-mail is already blacklisted";
          return;
        }

        $loggedin_username = $_SESSION[USERNAME];
        $data = array('action' => 'blacklist', 'email' => $email, 'admin' => $loggedin_username, 'return_url' => "administration.php");

        $url = "ban_user.php?".http_build_query($data, '', '&amp;');
        echo $url;
        header("Location: {$url}");
        exit;
      }

      if ($user_type != ADMIN) {
        doError("You need to be an administrator to view this page");
      } else {
        loadBannedUsers();
        if (empty($error_message)) {
          loadBlacklistedEmails();
        }
      }

      if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['form_id'])) {
          $form_id = $_POST['form_id'];

          if ($form_id == BAN) {
            banUser();
          } else if ($form_id == BLACKLIST) {
            blacklistUser();
          }
        }
      }
     ?>

     <!-- TODO need to find a way to return an error message from ban_user.php. Maybe a session variable -->

     <?php
        generateNavBar(ADMINISTRATION);
        $error_occurred = displayError();

        if (!$error_occurred):
      ?>
      <div class="container main-background">
        <div class="row mt-5 shadow card padding-1pcent">
          <h4>Banned Users</h4>
          <div class="row">
            <?php displayBannedUsers(); ?>
          </div>
          <div class="row">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <div class="form-group <?php echo (!empty($ban_error_message)) ? 'has-error' : ''; ?>">
                <div class="row">
                  <div class="col-auto">
                    <label>Username</label>
                  </div>
                  <div class="col-auto">
                    <input type="text" class="form-control" name="username" required>
                    <?php if (empty($ban_error_message)): ?>
                    <div class="form-text">
                      Enter the username of the user to ban
                    </div>
                    <?php else: ?>
                    <span class="help-block login-error-message"><?php echo $ban_error_message; ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="col-auto">
                    <label>Reason</label>
                  </div>
                  <div class="col-auto">
                    <input type="text" maxlength="64" class="form-control" name="reason" required>
                    <div class="form-text">
                      Max. 64 characters
                    </div>
                  </div>
                  <div class="col-auto">
                    <label>Until</label>
                  </div>
                  <div class="col-auto">
                    <input type="date" class="form-control" name="date_to" required>
                  </div>
                  <div class="col-auto">
                    <input type="time" class="form-control" name="time_to" required>
                  </div>
                  <div class="col-auto">
                    <button class="btn btn-danger">Ban</button>
                  </div>
                </div>
              </div>
              <input type="hidden" name="form_id" value="<?php echo BAN; ?>">
            </form>
          </div>
        </div>
        <div class="row mt-5 shadow card padding-1pcent">
          <h4>Blacklisted E-mails</h4>
          <div class="row">
            <?php displayBlacklistedEmails(); ?>
          </div>
          <div class="row">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <div class="form-group <?php echo (!empty($blacklist_error_message)) ? 'has-error' : ''; ?>">
                <div class="row">
                  <div class="col-auto">
                    <label>Email</label>
                  </div>
                  <div class="col-auto">
                    <input type="email" class="form-control" name="email" required>
                    <?php if (empty($blacklist_error_message)): ?>
                    <div class="form-text">
                      Enter the e-mail of the user to ban
                    </div>
                    <?php else: ?>
                    <span class="help-block login-error-message"><?php echo $blacklist_error_message; ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="col-auto">
                    <button class="btn btn-dark">Blacklist</button>
                  </div>
                </div>
              </div>
              <input type="hidden" name="form_id" value="<?php echo BLACKLIST; ?>">
            </form>
          </div>
        </div>

      <?php endif; ?>
      </div>
  </body>
</html>
