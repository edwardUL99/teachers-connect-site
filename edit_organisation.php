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
      require "organisation_variables.php";

      $name = $name_error = $headline = $about = $location = $password =
      $password_error = $confirm_password = $confirm_password_error = "";

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

      parseURL();

      $user_type = $_SESSION[USER_TYPE];
      $loggedin_username = $_SESSION[USERNAME];

      if ($user_type != ADMIN && $loggedin_username != $username) {
        doError("You can only edit a profile that is your own");
      } else {
        loadOrganisation($username);
        if (empty($error_message)) {
          $name = $organisation->name();
          $headline = $organisation->headline();
          $about = $organisation->about();
          $location = $organisation->location();
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
            <div class="form-group <?php echo (!empty($first_name_error)) ? 'has-error' : ''; ?>">
              <label>Name</label>
              <input type="text" pattern="[A-Za-z\- ]*" name="name" title="Please enter alphabetical characters only" class="form-control" placeholder="Organisation Name" value="<?php echo $name; ?>" required>
              <span class="help-block login-error-message"><?php echo $name_error; ?></span>
            </div>
            <div class="row">
              <div class="col-9">
                <div class="form-group">
                  <label>Headline</label>
                  <input type="text" name="headline" class="form-control" maxlength="64" placeholder="Headline" value="<?php echo $headline; ?>">
                  <div class="form-text">
                    Enter a short summary (up to 64 characters) outlining your organisation goal etc.
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
              <textarea class="form-control" rows="5" value="<?php echo $organisation->about(); ?>"></textarea>
              <div class="form-text">
                Enter a more detailed piece of information about your organisation here
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
      </div>
    <?php endif; ?>
  </body>
</html>
