<!DOCTYPE HTML>
<HTML>
    <HEAD>
        <title> Login Page </title>
        <link href="css/styles.css" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </HEAD>
    <Body>
      <?php
          require "database.php";
          require "constants.php";
          require "admin_profile_utils.php";

          session_start();

          $username = $username_error = "";
          $password = $password_error = "";

          /**
            * Gets the banned to date/time
            */
          function getBannedTo() {
            global $banned;
            global $blacklisted;
            global $username;

            if ($blacklisted) {
              return "Indefinitely";
            } else if ($banned) {
              global $conn;

              $sql = "SELECT date_to FROM banned_users WHERE username = ?;";

              if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $param_username);
                $param_username = $username;

                if ($stmt->execute()) {
                  $result = $stmt->get_result();

                  $date_to = null;
                  if ($result->num_rows == 1) {
                    while ($row = $result->fetch_assoc()) {
                      $date_to = $row['date_to'];
                      $date_to = strtotime($date_to);
                      $date_to = date('d/m/Y H:i', $date_to);
                    }
                  }

                  $stmt->close();
                  return $date_to;
                } else {
                  die("An error occurred: {$stmt->error}");
                }
              } else {
                die("An error occurred: {$conn->error}");
              }
            }
          }

          if($_SERVER['REQUEST_METHOD'] == "POST") {
            $username = $_POST["username"];
            $password = $_POST["password"];

            if (checkBanned() || checkBlacklist()) {
              $date_to = getBannedTo();
              $message = ($date_to == null) ? "":" until: {$date_to}";
              $username_error = "You have been banned from the site{$message}";
            } else {

              //prevent injections
              //The stripcslashes() function removes backslashes added by the addcslashes() function
              $username = stripcslashes($username);
              $password = stripcslashes($password);


              /*The function adds an escape character, the backslash, \, before certain potentially dangerous characters in a string passed in to the function.
                The characters escaped are \x00, \n, \r, \, ', " and \x1a.
                This can help prevent SQL injection attacks which are often performed by using the ' character to append malicious code to an SQL query.
                https://stackoverflow.com/questions/6327679/what-does-mysql-real-escape-string-really-do
                */
                $username = mysqli_real_escape_string($conn, $username);
                $password = mysqli_real_escape_string($conn, $password);

                if(!empty($username) && !empty($password)) {

                  //query db
                  $sql = "SELECT * FROM accounts WHERE username = \"{$username}\";";
                  $result = $conn->query($sql);

                  $row = mysqli_fetch_array($result);
                  if (isset($row)) {
                    if((password_verify($password, $row["password"]) || $row["password"] == $password)) {
                      $user_type = $row["type"];
                      if (isset($_POST["stay_signed_in"])) {
                        setcookie(USERNAME, $username, time() + (86400 * 30));
                        setcookie(USER_TYPE, $user_type, time() + (86400 * 30));
                      }
                      $_SESSION[LOGGED_IN] = true;
                      $_SESSION[USERNAME] = $username;
                      $_SESSION[USER_TYPE] = $user_type;
                      header("Location: feed.php");
                    } else {
                      $password_error = "Your password is incorrect";
                    }
                  } else {
                    $username_error = "The username does not exist";
                  }
                } else {
                  if (empty($username)) {
                    $username_error = "Please enter your username";
                  }

                  if (empty($password)) {
                    $password_error = "Please enter your password";
                  }
                }


                $conn->close();
              }
            }
      ?>
      <div class="container-fluid main-background overflow-auto flex-fill">
        <div class="row">
          <div class="col shadow login-container min-vh-100">
            <div class="col d-flex flex-column login-container-content">
              <img class="img-fluid" src="images/logo.png" alt="Teacher's Connect logo">
              <div class="row">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method ="POST">
                  <div class="form-group login-input <?php echo (!empty($username_error)) ? 'has-error' : ''; ?>">
                    <label>Username</label>
                    <input type="text"  name = "username" pattern="[A-Za-z0-9_\-]*" title="Please enter alphanumeric characters only" class="form-control" value="<?php echo $username; ?>" required>
                    <span class="help-block login-error-message"><?php echo $username_error; ?></span>
                  </div>
                  <div class="form-group login-input <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                    <label>Password</label>
                    <input type="password" name = "password" title="Enter your password" class="form-control" required>
                    <span class="help-block login-error-message"><?php echo $password_error; ?></span>
                  </div>
                  <div class="form-group checkbox login-input">
                    <div class="row">
                      <div class="col">
                        <label><input type="checkbox" name="stay_signed_in">Stay signed in</label>
                      </div>
                      <div class="col align-self-end text-end">
                        <a href="url">Forgot password?</a>
                      </div>
                    </div>
                  </div>
                  <div class="row login-button">
                    <button class="btn btn-primary login-button col" type="submit">Login</button>
                  </div>
                  <div class="row">
                    <div class="col align-self-center text-center">
                      <H3>Need an account?</H3>
                      <a href = "teacher_signup.php">Sign up</a>
                      <div class="align-self-center separator">or</div>
                      <a href = "organisation_signup.php">Create an Organisation</a>
                    </div>
                  </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    </Body>
</HTML>
