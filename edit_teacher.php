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
      $confirm_password = $confirm_password_error = ""; // TODO add variables for education history here

      $current_organisation = null;
      $available_organisations = array();
      $available_degrees = array();
      $user_qualifications = array();
      $skills_options = array();

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
          } else {
            doSQLError($stmt);
          }

          $stmt->close();
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
        echo "<option value=\"-1\">No Organisation</option>";
        foreach ($available_organisations as $key => $value) {
          $selected = ($key == $curr_org_id) ? "selected":"";
          echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
        }
      }

      /**
        * Load academic degrees
        */
      function loadDegrees() {
        global $available_degrees;
        global $conn;

        $sql = "SELECT * FROM academic_degrees;";

        if ($stmt = $conn->prepare($sql)) {
          if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $degree_id = $row['degree_id'];
                $available_degrees[$degree_id] = new AcademicDegree($degree_id,
                $row['title'], $row['type'], $row['school'], $row['description'], $row['level']);
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
        * Get the options for a degree options select
        */
      function getDegreeOptions() {
        global $available_degrees;

        echo "<option selected>Choose a degree</option>";
        foreach ($available_degrees as $key => $value) {
          $title = $value->title();
          $school = $value->school();
          $text = "{$title} - {$school}";
          echo "<option value=\"{$key}\">{$text}</option>";
        }

        echo "<option>New degree</option>";
      }

      /**
        * Loads the user's qualifictions
        */
      function loadUserQualifications() {
        global $available_degrees;
        global $user_qualifications;
        global $teacher;
        global $conn;

        $sql = "SELECT * FROM qualifications WHERE username = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_username);
          $param_username = $teacher->username();

          if ($stmt->execute()) {
            $results = $stmt->get_result();

            if ($results->num_rows > 0) {
              while ($row = $results->fetch_assoc()) {
                $timestamp = strtotime($row['date_obtained']);
                $timestamp = date('d/m/Y', $timestamp);
                $user_qualifications[] = new Qualification($teacher, $available_degrees[$row['degree_id']], $timestamp);
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
        * Retrieve the options for the user's qualifications
        */
      function getQualificationsOptions() {
        global $user_qualifications;

        echo "<option>Choose a qualification</option>";
        foreach ($user_qualifications as $key => $value) {
          $degree = $value->degree();
          $text = "{$degree->title()} - {$value->date_obtained()} - {$degree->school()}";
          echo "<option value=\"{$degree->degree_id()}-{$value->teacher()->username()}\">{$text}</option>";
        }
      }

      /**
        * Loads teacher's skills
        */
      function loadSkillsOptions() {
        global $username;
        global $conn;
        global $skills_options;

        $sql = "SELECT * FROM teacher_skills NATURAL JOIN skills WHERE username = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_user);
          $param_user = $username;

          if ($stmt->execute()) {
            $results = $stmt->get_result();

            if ($results->num_rows > 0) {
              while ($row = $results->fetch_assoc()) {
                $value = $row['skill_id'];
                $text = $row['name'];
                $skills_options[] = "<option value=\"{$value}\">{$text}</option>";
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
        * Get the skills options for the teacher to choose from
        */
      function getSkillsOptions() {
        global $skills_options;

        foreach ($skills_options as $key => $value) {
          echo $value;
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
          if (empty($error_message)) {
            loadDegrees();
            if (empty($error_message)) {
              loadUserQualifications();
              if (empty($error_message)) {
                loadSkillsOptions();
              }
            }
          }
        }
      }
     ?>

     <?php
        generateNavBar(PROFILE);
        $error_occurred = displayError();

        if (!$error_occurred):
      ?>
      <div class="container main-background">
        <div class="row mt-5 shadow card padding-1pcent" id="update_profile">
          <h4>Update Profile</h4>
          <form id="update_profile_form">
            <div class="row">
              <div class="col">
                <div class="form-group <?php echo (!empty($first_name_error)) ? 'has-error' : ''; ?>">
                  <label>First Name</label>
                  <input type="text" pattern="[A-Za-z\-]*" name="first_name" id="first_name" title="Please enter alphabetical characters only" class="form-control" placeholder="John" value="<?php echo $first_name; ?>" required>
                  <span class="help-block login-error-message"><?php echo $first_name_error; ?></span>
                </div>
              </div>
              <div class="col">
                <div class="form-group <?php echo (!empty($last_name_error)) ? 'has-error' : ''; ?>">
                  <label>Last Name</label>
                  <input type="text" pattern="[A-Za-z\-]*" name="last_name" id="last_name" title="Please enter alphabetical characters only" class="form-control" placeholder="Doe" value="<?php echo $last_name; ?>" required>
                  <span class="help-block login-error-message"><?php echo $last_name_error; ?></span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-9">
                <div class="form-group">
                  <label>Headline</label>
                  <input type="text" name="headline" id="headline" class="form-control" maxlength="64" placeholder="Headline" value="<?php echo $headline; ?>">
                  <div class="form-text">
                    Enter a short summary (up to 64 characters) outlining your current position, job you're seeking etc.
                  </div>
                </div>
              </div>
              <div class="col-3">
                <div class="form-group">
                  <label>Location</label>
                  <input type="text" name="location" id="location" class="form-control" maxlength="64" placeholder="Location" value="<?php echo $location; ?>">
                  <div class="form-text">
                    Enter your location, e.g. City, Country
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>About</label>
              <textarea name="about" id="about" class="form-control" rows="5"><?php echo $teacher->about(); ?></textarea>
              <div class="form-text">
                Enter a more detailed piece of information about yourself here
              </div>
            </div>
            <div class="row text-end">
              <div class="col">
                <button type="button" onclick="handleUpdateProfile();" class="btn btn-primary">Save</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row mt-5 shadow card padding-1pcent" id="update_password">
          <h4>Update Password</h4>
          <form id="update_password_form">
            <div class="row">
              <div class="col">
                <div class="form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>">
                  <label>Password</label>
                  <input type="password" name="password" id="password" oninput="onPasswordInput();" minlength="8" class="form-control" required>
                  <span class="help-block login-error-message"><?php echo $password_error; ?></span>
                  <div class="form-text">
                    Enter your new password
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="form-group <?php echo (!empty($confirm_password_error)) ? 'has-error' : ''; ?>">
                  <label>Confirm Password</label>
                  <input type="password" name="confirm_password" id="confirm_password" oninput="onConfirmPasswordInput();" minlength="8" class="form-control" required>
                  <span class="help-block login-error-message"><?php echo $confirm_password_error; ?></span>
                  <div class="form-text">
                    Confirm your new password
                  </div>
                </div>
              </div>
            </div>
            <div class="row text-end">
              <div class="col">
                <button type="button" onclick="handleUpdatePassword();" class="btn btn-primary" id="password_button">Save</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row mt-5 shadow card padding-1pcent" id="join_organisation">
          <h4>Join Organisation</h4>
          <form id="join_organisation_form">
            <div class="form-group">
              <select class="form-select" id="organisation_choice" onchange="onOrganisationChosen();" name="current_organisation">
                <?php getOrganisationOptions(); ?>
              </select>
              <div class="form-text">
                Choose an existing organisation to join. Choose "No Organisation" to remove your current one if any
              </div>
            </div>
            <div class="row text-end">
              <div class="col">
                <button type="button" onclick="handleJoinOrganisation();" id="join_button" class="btn btn-primary">Join Organisation</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row mt-5 shadow card padding-1pcent" id="education_history">
          <h4>Add Education History</h4>
          <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="new_education_form">
            <div class="row">
              <div class="col">
                <div class="form-group">
                  <label>Degree</label>
                  <select class="form-select" id="degree_choice" onchange="onDegreeChosen();" name="education_choice">
                    <?php getDegreeOptions(); ?>
                  </select>
                  <div class="form-text">
                    Select the degree obtained. If you can't find it, choose New degree
                  </div>
                </div>
              </div>
              <div class="col">
                <div class="form-group">
                  <label>Date Obtained</label>
                  <input type="date" class="form-control" name="date_obtained" id="date_obtained" required>
                  <div class="form-text">
                    Enter the date at which you got your degree
                  </div>
                </div>
              </div>
            </div>
            <div id="new_degree">
              <div class="row">
                <div class="col">
                  <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="degree_title" id="degree_title" required>
                    <div class="form-text">
                      Enter the title of the degree
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="form-group">
                    <label>Type</label>
                    <input type="text" class="form-control" name="degree_type" id="degree_type" required>
                    <div class="form-text">
                      Enter the type of your degree (e.g. Bsc or Bachelors of Science etc.)
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <div class="form-group">
                    <label>School</label>
                    <input type="text" class="form-control" name="school" id="school" required>
                    <div class="form-text">
                      Enter the school you got your degree from
                    </div>
                  </div>
                </div>
                <div class="col">
                  <div class="form-group">
                    <label>Level</label>
                    <input type="text" class="form-control" name="level" id="level" required>
                    <div class="form-text">
                      Enter the level of this degree (e.g. High School, University etc.)
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" maxlength="255" rows="3" class="form-control"></textarea>
                <div class="form-text">
                  Enter a description of the degree (topics covered etc.) in max 255 characters
                </div>
              </div>
            </div>
            <div class="row text-end">
              <div class="col">
                <button type="button" onclick="handleNewEducationHistory();" class="btn btn-primary" id="degree_button">Save</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row mt-5 shadow card padding-1pcent" id="delete_education">
          <h4>Remove Education History</h4>
          <form id="delete_education_form">
            <select class="form-select" id="remove_qualification_choice" onchange="onQualificationChosen();" name="chosen_qualification">
              <?php getQualificationsOptions(); ?>
            </select>
            <div class="row text-end mt-2">
              <div class="col">
                <button type="button" onclick="handleRemoveQualification();" id="remove_button" class="btn btn-primary">Remove</button>
              </div>
            </div>
          </form>
        </div>
        <div class="row shadow card padding-1pcent mt-5"> <!-- TODO need to figure out col spacing -->
          <div class="col padding-1pcent" id="add_skills">
            <h4>Add Skills</h4>
            <form id="add_skills_form">
              <div class="form-group">
                <label>Skills</label>
                <input type="text" name="skills" id="skills" oninput="onSkillsInput();" class="form-control" required>
                <div class="form-text">
                  Enter skills to add in a comma-separated (,) list
                </div>
              </div>
              <div class="row text-end mt-2">
                <div class="col">
                  <button type="button" onClick="handleNewSkills();" id="add_skills_button" class="btn btn-primary">Add</button>
                </div>
              </div>
            </form>
          </div>
          <div class="col padding-1pcent" id="remove_skill">
            <h4>Remove Skills</h4>
            <form id="remove_skill_form">
              <div class="form-group">
                <label>Choose skills to remove</label>
                <select class="form-select" multiple id="skills_choice" onchange="onSkillChosen();" name="skills_choice">
                  <?php getSkillsOptions(); ?>
                </select>
                <div class="form-text">
                  Select the skill to remove from the list
                </div>
              </div>
              <div class="row text-end mt-2">
                <div class="col">
                  <button type="button" onClick="handleDeleteSkill();" id="remove_skill_button" class="btn btn-primary">Remove</button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="row mt-5 shadow card padding-1pcent" id="delete_account">
          <h4>Delete Account</h4>
          <p>This form is used to delete your account. To delete it, you will have to enter your password to confirm. Please note that
            this action is irreversible</p>
          <form id="delete_account_form">
            <div class="form-group">
              <label>Password</label>
              <input type="password" minlength="8" class="form-control" name="delete_password" id="delete_password" required>
              <div class="form-text">
                Enter your account password
              </div>
            </div>
            <div class="row text-end mt-2">
              <div class="col">
                <button type="button" onclick="handleDeleteAccount();" id="remove_button" class="btn btn-danger">Delete</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <script type="text/javascript" src="forms.js"></script>
    <script type="text/javascript" src="ajax.js"></script>
    <script>
      const username = <?php echo json_encode($username); ?>;
      const org_choose_message = "Choose an organisation";
      const degree_choose_message = "Choose a degree";
      const new_degree_message = "New degree";
      const remove_degree_message = "Choose a qualification";
      const choose_skill_message = "Choose skills";

      const join_button = document.getElementById('join_button');
      const organisation_choice = document.getElementById('organisation_choice');
      join_button.disabled = organisation_choice.value == org_choose_message;

      const degree_button = document.getElementById('degree_button');
      const degree_choice = document.getElementById('degree_choice');
      degree_button.disabled = degree_choice.value == degree_choose_message;

      var visible;

      const new_degree = document.getElementById('new_degree');
      handleAddDegreeDisplay();

      const password = document.getElementById('password');
      const confirm_password = document.getElementById('confirm_password');
      confirm_password.disabled = true;
      const password_button = document.getElementById('password_button');
      password_button.disabled = true;

      const remove_button = document.getElementById('remove_button');
      const remove_qualification_choise = document.getElementById('remove_qualification_choice');
      remove_button.disabled = remove_qualification_choice.value == remove_degree_message;

      const add_skills_button = document.getElementById('add_skills_button');
      const skills = document.getElementById('skills');
      add_skills_button.disabled = true;

      const remove_skill_button = document.getElementById('remove_skill_button');
      const skills_choice = document.getElementById('skills_choice');
      remove_skill_button.disabled = true;

      /**
        * Handles the update of the profile
        */
      function handleUpdateProfile() {
        var valid = validateForm('update_profile_form');
        if (valid) {
          var data = serializeForm('update_profile', 'input,textarea');
          data['username'] = username;
          data['edit_type'] = "teacher";
          data['edit_form'] = "update_profile";

          var ajax = getAJAX();
          if (ajax != null) {
            ajax.onreadystatechange = function() {
              if (ajax.readyState == 4) {
                var response = ajax.response;

                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;

                  if (success && message == "UPDATED") {
                    alert("Profile has been updated");
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(response);
                }
              }
            }
          }

          ajax.open("POST", "edit-profile-ajax.php", true);
          ajax.send(JSON.stringify(data));
        }

        return false;
      }

      /**
        * Handles the update of the password
        */
      function handleUpdatePassword() {
        var valid = validateForm('update_password_form');
        if (valid) {
          var data = serializeForm('update_password', 'input');
          data['username'] = username;
          data['edit_type'] = "teacher";
          data['edit_form'] = "update_password";

          var ajax = getAJAX();
          if (ajax != null) {
            ajax.onreadystatechange = function() {
              if (ajax.readyState == 4) {
                var response = ajax.response;

                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;

                  if (success && message == "UPDATED") {
                    alert("Profile has been updated");
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(response);
                }
              }
            }
          }

          ajax.open("POST", "edit-profile-ajax.php", true);
          ajax.send(JSON.stringify(data));
        }

        return false;
      }

      /**
        * Handles password input
        */
      function onPasswordInput() {
        document.getElementById('update_password_form').classList.add("was-validated");
        var text = password.value;

        if (text.length < 8) {
          confirm_password.disabled = true;
          var remaining = 8 - text.length;
          password.setCustomValidity("You need to enter " + remaining + " more characters");
        } else {
          password.setCustomValidity("");
          confirm_password.disabled = false;
        }

        if (!confirm_password.disabled && confirm_password.value.length > 0)
          onConfirmPasswordInput(); // update the validity of the confirm password field
      }

      /**
        * Handles confirm password input
        */
      function onConfirmPasswordInput() {
        var text = confirm_password.value;

        if (text != password.value) {
          confirm_password.setCustomValidity("The passwords do not match");
          password_button.disabled = true;
        } else {
          confirm_password.setCustomValidity("");
          password_button.disabled = false;
        }
      }

      /**
        * Handles the request to join an organisation
        */
      function handleJoinOrganisation() {
        var valid = validateForm('join_organisation_form');
        if (valid) {
          var data = serializeForm('join_organisation', 'select');
          data['username'] = username;
          data['edit_type'] = "teacher";
          data['edit_form'] = "join_organisation";

          var ajax = getAJAX();
          if (ajax != null) {
            ajax.onreadystatechange = function() {
              if (ajax.readyState == 4) {
                var response = ajax.response;

                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;

                  if (success && message == "UPDATED") {
                    alert("Profile has been updated");
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(response);
                }
              }
            }
          }

          ajax.open("POST", "edit-profile-ajax.php", true);
          ajax.send(JSON.stringify(data));
        }

        return false;
      }

      /**
        * Handles when an organisation is chosen
        */
      function onOrganisationChosen() {
        join_button.disabled = organisation_choice.value == org_choose_message;
      }

      /**
        * Handles when a degree is chosen
        */
      function onDegreeChosen() {
        degree_button.disabled = degree_choice.value == degree_choose_message;
        handleAddDegreeDisplay();
      }

      /**
        * Handle the display/hiding of the new degree form
        */
      function handleAddDegreeDisplay() {
        visible = degree_choice.value == new_degree_message ? "block":"none";
        new_degree.style.display = visible;

        var required;
        if (visible == "block") {
          new_degree.classList.remove("hidden-form");
          required = true;
        } else {
          new_degree.classList.add("hidden-form");
          required = false;
        }

        var inputs = new_degree.querySelectorAll('input,textarea');
        for (var item of inputs) {
          item.required = required;
        }
      }

      /**
        * Handles the save of new education history
        */
      function handleNewEducationHistory() {
        var valid = validateForm('new_education_form');
        if (valid) {
          var data = serializeForm('education_history', 'input,textarea,select');
          data['username'] = username;
          data['edit_type'] = "teacher";
          data['edit_form'] = "education_history";

          if (visible == "none") {
            delete data.degree_title;
            delete data.degree_type;
            delete data.school;
            delete data.level;
            delete data.description;
          }

          var ajax = getAJAX();
          if (ajax != null) {
            ajax.onreadystatechange = function() {
              if (ajax.readyState == 4) {
                var response = ajax.response;

                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;

                  if (success && message == "UPDATED") {
                    alert("Profile has been updated");
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(response);
                }
              }
            }

            ajax.open("POST", "edit-profile-ajax.php", true);
            ajax.send(JSON.stringify(data));
          }
        }

        return false;
      }

      /**
        * Handles a qualification choosen to be removed
        */
      function onQualificationChosen() {
        remove_button.disabled = remove_qualification_choice.value == remove_degree_message;
      }

      /**
        * Handles the removal of a qualification
        */
      function handleRemoveQualification() {
        var valid = validateForm('delete_education_form');
        if (valid) {
          var data = serializeForm('delete_education', 'select');
          data['username'] = username;
          data['edit_type'] = "teacher";
          data['edit_form'] = "delete_education";

          var ajax = getAJAX();
          if (ajax != null) {
            ajax.onreadystatechange = function() {
              if (ajax.readyState == 4) {
                var response = ajax.response;

                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;

                  if (success && message == "UPDATED") {
                    alert("Profile has been updated");
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(response);
                }
              }
            }

            ajax.open("POST", "edit-profile-ajax.php", true);
            ajax.send(JSON.stringify(data));
          }
        }

        return false;
      }

      /**
        * Handles input in the skills input
        */
      function onSkillsInput() {
        add_skills_button.disabled = skills.value.length == 0;
      }

      /**
        * Handles adding new skills to the user's profile
        */
      function handleNewSkills() {
        var valid = validateForm('add_skills_form');
        if (valid) {
          var data = serializeForm('add_skills', 'input');
          data['username'] = username;
          data['edit_type'] = "teacher";
          data['edit_form'] = "add_skills";

          var ajax = getAJAX();
          if (ajax != null) {
            ajax.onreadystatechange = function() {
              if (ajax.readyState == 4) {
                var response = ajax.response;

                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;

                  if (success && message == "UPDATED") {
                    alert("Profile has been updated");
                    var data = responseBody.data;

                    for (var property in data) {
                      addToSelect('skills_choice', property, data[property]);
                    }
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(response);
                }
              }
            }
          }

          ajax.open("POST", "edit-profile-ajax.php", true);
          ajax.send(JSON.stringify(data));
        }

        return false;
      }

      /**
        * Get the number of skills selected to be removed
        */
      function getNumberSkillsSelected() {
        var numberSelected = 0;
        for (var i = 0; i < skills_choice.options.length; i++) {
          if (skills_choice.options[i].selected) {
            numberSelected++;
          }
        }

        return numberSelected;
      }

      /**
        * Handles the selection of a skill to remove
        */
      function onSkillChosen() {
        remove_skill_button.disabled = getNumberSkillsSelected() == 0;
      }

      /**
        * Handles the removal of a skill from the user's profile
        */
      function handleDeleteSkill() {
        var valid = validateForm('remove_skill_form');
        if (valid) {
          var data = serializeForm('remove_skill', 'select');
          data['username'] = username;
          data['edit_type'] = "teacher";
          data['edit_form'] = "remove_skill";

          var ajax = getAJAX();
          if (ajax != null) {
            ajax.onreadystatechange = function() {
              if (ajax.readyState == 4) {
                var response = ajax.response;

                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;

                  if (success && message == "UPDATED") {
                    alert("Profile has been updated");

                    var data = responseBody.data;

                    for (var property in data) {
                      removeFromSelectByValue('skills_choice', data[property]);
                    }
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(response);
                }
              }
            }
          }

          ajax.open("POST", "edit-profile-ajax.php", true);
          ajax.send(JSON.stringify(data));
        }

        return false;
      }

      /**
        * Handles the deletion of the account
        */
      function handleDeleteAccount() {
        var valid = validateForm('delete_account_form');
        if (valid) {
          var data = serializeForm('delete_account', 'input');
          data['username'] = username;
          data['edit_type'] = "teacher";
          data['edit_form'] = "delete_account";

          var ajax = getAJAX();
          if (ajax != null) {
            ajax.onreadystatechange = function() {
              if (ajax.readyState == 4) {
                var response = ajax.response;

                try {
                  var responseBody = JSON.parse(response);
                  var success = responseBody.success;
                  var message = responseBody.message;

                  if (success && message == "DELETED") {
                    alert("Account has been deleted");
                    window.location.href = "logout.php";
                  } else {
                    alert(message);
                  }
                } catch (e) {
                  alert(response);
                }
              }
            }

            ajax.open("POST", "edit-profile-ajax.php", true);
            ajax.send(JSON.stringify(data));
          }
        }

        return false;
      }
    </script>
  </body>
</html>
