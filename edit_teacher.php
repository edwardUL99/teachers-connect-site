<!DOCTYPE html>
<html>
  <head>
    <title>Edit Teacher Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link type="text/css" href="css/styles.css" rel="stylesheet">
    <link type="text/css" href="css/feed.css" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <?php
      require "navbar.php";
      require "teacher_variables.php";

      $first_name = $first_name_error = $last_name = $last_name_error = $headline
      = $about = $location = $current_org_error = $password = $password_error =
      $confirm_password = $confirm_password_error = "";

      $current_organisation = null;
      $available_organisations = array();

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
        * Load all organisations into the $available_organisations array
        */
      function loadOrganisations() {
        global $available_organisations;
        global $conn;

        $sql = "SELECT organisation_id, name FROM organisations WHERE username IS NOT NULL;";

        if ($stmt = $conn->prepare($sql)) {
          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $available_organisations[$row['organisation_id']] = $row['name'];
              }
            }

            $stmt->close();
          } else {
            doSQLError($stmt);
          }
        } else {
          doSQLError($conn->error);
        }
      }

      /**
        * Gets list of organisations for the user to join
        */
      function getOrganisationOptions() {
        global $available_organisations;
        global $current_organisation;

        $curr_org_id = ($current_organisation != null) ? $current_organisation->organisation_id():-1;
        $selected = ($current_organisation == null) ? "selected":"";
        echo "<option {$selected}>Choose an organisation</option>";
        foreach ($available_organisations as $key => $value) {
          $selected = ($key == $curr_org_id) ? "selected":"";
          echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
        }
      }

      parseURL();

      $loggedin_username = $_SESSION[USERNAME];
      $user_type = $_SESSION[USER_TYPE];

      if ($user_type != ADMIN && $loggedin_username != $username) {
        doError("You can only edit a profile that is your own");
      } else {
        loadTeacher($username);
        if (empty($error_message)) {
          $first_name = $teacher->firstName();
          $last_name = $teacher->lastName();
          $headline = $teacher->headline();
          $about = $teacher->about();
          $location = $teacher->location();

          loadOrganisations();
        }
      }
     ?>

     <?php
        generateNavBar(PROFILE);
        $error_occurred = displayError();

        if (!$error_occurred):
      ?>
      <div class="container main-background">
        <div class="row mt-5 shadow card padding-1pcent">
          <h4>Update Profile</h4>
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="row">
              <div class="col">
                <div class="form-group <?php echo (!empty($first_name_error)) ? 'has-error' : ''; ?>">
                  <label>First Name</label>
                  <input type="text" pattern="[A-Za-z\-]*" name="first_name" title="Please enter alphabetical characters only" class="form-control" placeholder="John" value="<?php echo $first_name; ?>" required>
                  <span class="help-block login-error-message"><?php echo $first_name_error; ?></span>
                </div>
              </div>
              <div class="col">
                <div class="form-group <?php echo (!empty($last_name_error)) ? 'has-error' : ''; ?>">
                  <label>Last Name</label>
                  <input type="text" pattern="[A-Za-z\-]*" name="first_name" title="Please enter alphabetical characters only" class="form-control" placeholder="Doe" value="<?php echo $last_name; ?>" required>
                  <span class="help-block login-error-message"><?php echo $last_name_error; ?></span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-9">
                <div class="form-group">
                  <label>Headline</label>
                  <input type="text" name="headline" class="form-control" maxlength="64" placeholder="Headline" value="<?php echo $headline; ?>">
                  <div class="form-text">
                    Enter a short summary (up to 64 characters) outlining your current position, job you're seeking etc.
                  </div>
                </div>
              </div>
              <div class="col-3">
                <div class="form-group">
                  <label>Location</label>
                  <input type="text" name="location" class="form-control" maxlength="64" placeholder="Location" value="<?php echo $location; ?>">
                  <div class="form-text">
                    Enter your location, e.g. City, Country
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>About</label>
              <textarea class="form-control" rows="5"><?php echo $teacher->about(); ?></textarea>
              <div class="form-text">
                Enter a more detailed piece of information about yourself here
              </div>
            </div>
            <div class="row text-end">
              <div class="col">
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row mt-5 shadow card padding-1pcent">
          <h4>Update Password</h4>
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="row">
              <div class="col">
                <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                  <label>Password</label>
                  <input type="password" name="password" minlength="8" class="form-control" required>
                  <span class="help-block login-error-message"><?php echo $password_error; ?></span>
                  <div class="form-text">
                    Enter your new password
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="form-group <?php echo (!empty($confirm_password_error)) ? 'has-error' : ''; ?>">
                  <label>Confirm Password</label>
                  <input type="password" name="confirm_password" minlength="8" class="form-control" required>
                  <span class="help-block login-error-message"><?php echo $confirm_password_error; ?></span>
                  <div class="form-text">
                    Confirm your new password
                  </div>
                </div>
              </div>
            </div>
            <div class="row text-end">
              <div class="col">
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row mt-5 shadow card padding-1pcent">
          <h4>Join Organisation</h4>
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <select class="form-select" name="current_organisation">
              <?php getOrganisationOptions(); ?>
            </select>
            <div class="row text-end mt-2">
              <div class="col">
                <button type="submit" class="btn btn-primary">Join Organisation</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </body>
</html>
