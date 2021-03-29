<?php
  /**
    * This script updates a profile using ajax. It allows processing of both
    * teachers and organisation edits.
    * Pass in the USERNAME of the username being edited also.
    * Pass in the constants EDIT_TYPE which has the type of profile (TEACHER/ORGANISATION) to edit.
    * Then pass in the EDIT_FORM constant with the id of the form constant (should match the id in the html)
    * This assumes all the relevant data is passed in
    */
    require "database.php";
    require "constants.php";
    require "ajax.php";

    $username = "";
    $edit_type = "";
    $edit_form = "";

    /**
      * Throw error
      */
    function throwError() {
      die("Script called with missing variables");
    }

    /**
      * Parses any post parameters
      */
    function edit_profile_parsePOST() {
      global $username;
      global $edit_type;
      global $edit_form;

      if (isset($_POST[USERNAME])) {
        $username = $_POST[USERNAME];
      } else {
        throwError();
      }

      if (isset($_POST[EDIT_TYPE])) {
        $edit_type = $_POST[EDIT_TYPE];
      } else {
        throwError();
      }

      if (isset($_POST[EDIT_FORM])) {
        $edit_form = $_POST[EDIT_FORM];
      } else {
        throwError();
      }
    }

    /**
      * Process update of teacher profile
      */
    function processTeacherProfileUpdate() {
      global $username;
      global $conn;

      $first_name = (isset($_POST['first_name'])) ? $_POST['first_name']:null;

      if ($first_name == null) {
        respond(false, "First name is a mandatory field");
      }

      $last_name = (isset($_POST['last_name'])) ? $_POST['last_name']:null;

      if ($last_name == null) {
        respond(false, "Last name is a mandatory field");
      }

      $headline = (isset($_POST['headline'])) ? $_POST['headline']:null;
      $location = (isset($_POST['location'])) ? $_POST['location']:null;
      $about = (isset($_POST['about'])) ? $_POST['about']:null;

      $sql = "UPDATE teachers SET first_name = ?, last_name = ?, headline = ?, about = ?, location = ?
      WHERE username = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssss", $param_first, $param_last, $param_headline, $param_about, $param_location, $param_username);
        $param_first = $first_name;
        $param_last = $last_name;
        $param_headline = $headline;
        $param_about = $about;
        $param_location = $location;
        $param_username = $username;

        if (!$stmt->execute()) {
          die("Database error: {$stmt->error}");
        }

        respond(true, "UPDATED");
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Process update of organisation profile
      */
    function processOrganisationProfileUpdate() {
      global $username;
      global $conn;

      $name = (isset($_POST['name'])) ? $_POST['name']:null;

      if ($name == null) {
        respond(false, "Name is a mandatory field");
      }

      $headline = (isset($_POST['headline'])) ? $_POST['headline']:null;
      $location = (isset($_POST['location'])) ? $_POST['location']:null;
      $about = (isset($_POST['about'])) ? $_POST['about']:null;

      $sql = "UPDATE organisations SET name = ?, headline = ?, about = ?, location = ?
      WHERE username = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssss", $param_name, $param_headline, $param_about, $param_location, $param_username);
        $param_name = $name;
        $param_headline = $headline;
        $param_about = $about;
        $param_location = $location;
        $param_username = $username;

        if (!$stmt->execute()) {
          die("Database error: {$stmt->error}");
        }

        respond(true, "UPDATED");
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Process the update of the profile information
      */
    function processProfileUpdate() {
      global $edit_type;

      if ($edit_type == TEACHER) {
        processTeacherProfileUpdate();
      } else {
        processOrganisationProfileUpdate();
      }
    }

    /**
      * Process update of the user's password
      */
    function processPasswordUpdate() {
      global $username;
      global $conn;

      $password = (isset($_POST['password'])) ? $_POST['password']:null;

      if ($password == null) {
        respond(false, "Password is a mandatory field");
      }

      $confirm_password = (isset($_POST['confirm_password'])) ? $_POST['confirm_password']:null;

      if ($confirm_password == null) {
        respond(false, "Confirm Password is a mandatory field");
      }

      if (strlen($password) < 8) {
        respond(false, "The password needs to be 8 or more characters");
      } else if ($password != $confirm_password) {
        respond(false, "The passwords do not match");
      }

      $sql = "UPDATE accounts SET password = ? WHERE username = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $param_pass, $param_username);
        $param_pass = password_hash($password, PASSWORD_DEFAULT);
        $param_username = $username;

        if (!$stmt->execute()) {
          die("Database error: {$stmt->error}");
        }

        respond(true, "UPDATED");
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Check if a user is already connected or not
      */
    function alreadyConnected() {
      global $conn;
      global $username;

      $sql = "SELECT * FROM organisation_members WHERE teacher_username = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_username);
        $param_username = $username;

        if ($stmt->execute()) {
          $result = $stmt->get_result();
          $stmt->close();
          return $result->num_rows == 1;
        } else {
          die("Database error: {$stmt->error}");
        }
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Process joining organisation
      */
    function processJoinOrganisation() {
      global $username;
      global $conn;

      if (isset($_POST['organisation_choice'])) {
        $value = $_POST['organisation_choice'];
        $sql = "";
        $delete = $value == -1;
        $already_connected = alreadyConnected();

        if ($delete) {
          $sql = "DELETE FROM organisation_members WHERE teacher_username = ?;";
        } else if ($already_connected) {
          $sql = "UPDATE organisation_members SET organisation_id = ? WHERE teacher_username = ?;";
        } else {
          $sql = "INSERT INTO organisation_members (teacher_username, organisation_id) VALUES (?, ?);";
        }

        if ($stmt = $conn->prepare($sql)) {
          if ($delete) {
            $stmt->bind_param("s", $param_user);
            $param_user = $username;
          } else if ($already_connected) {
            $stmt->bind_param("ss", $param_org_id, $param_username);
            $param_org_id = $value;
            $param_username = $username;
          } else {
            $stmt->bind_param("ss", $param_username, $param_org_id);
            $param_username = $username;
            $param_org_id = $value;
          }

          if (!$stmt->execute()) {
            die("Database error: {$stmt->error}");
          }

          $stmt->close();
          respond(true, "UPDATED");
        } else {
          die("Database error: {$conn->error}");
        }
      }
    }

    // TODO education history functions etc. here

    /**
      * Creates a new academic degree in the database and returns the id
      */
    function createAcademicDegree() {
      global $conn;

      $degree_title = (isset($_POST['degree_title'])) ? $_POST['degree_title']:null;
      if ($degree_title == null) {
        respond(false, "Degree Title is a mandatory field");
      }

      $degree_type = (isset($_POST['degree_type'])) ? $_POST['degree_type']:null;
      if ($degree_type == null) {
        respond(false, "Degree Type is a mandatory field");
      }

      $school = (isset($_POST['school'])) ? $_POST['school']:null;
      if ($school == null) {
        respond(false, "School is a mandatory field");
      }

      $level = (isset($_POST['level'])) ? $_POST['level']:null;
      if ($level == null) {
        respond(false, "Level is a mandatory field");
      }

      $description = (isset($_POST['description'])) ? $_POST['description']:null;
      if ($description == null) {
        respond(false, "Description is a mandatory field");
      }

      $sql = "INSERT INTO academic_degrees (title, type, school, description, level) VALUES (?, ?, ?, ?, ?);";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssss", $param_title, $param_type, $param_school, $param_description, $param_level);
        $param_title = $degree_title;
        $param_type = $degree_type;
        $param_school = $school;
        $param_level = $level;
        $param_description = $description;

        if (!$stmt->execute()) {
          die("Database error: {$stmt->error}");
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return $id;
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Checks if a qualification already exists and responds if so
      */
    function qualificationAlreadyExists($username, $degree_id) {
      global $conn;

      $sql = "SELECT * FROM qualifications WHERE username = ? AND degree_id = ?";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $param_username, $param_degree);
        $param_username = $username;
        $param_degree = $degree_id;

        if ($stmt->execute()) {
          $result = $stmt->get_result();

          if ($result->num_rows >= 1) {
            respond(false, "This user already has this qualification");
          }
        } else {
          die("Database error: {$stmt->error}");
        }

        $stmt->close();
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Processes the creation of an education history object
      */
    function processEducationHistory() {
      global $username;
      global $conn;

      if (isset($_POST['degree_choice'])) {
        $value = $_POST['degree_choice'];

        $degree_id = 0;
        if ($value == "New degree") {
          $degree_id = createAcademicDegree(); // this method will respond with an error if something happens
        } else {
          $degree_id = $value;
        }

        $date_obtained = (isset($_POST['date_obtained'])) ? $_POST['date_obtained']:null;

        if ($date_obtained == null) {
          respond(false, "Date Obtained is a mandatory field");
        }

        qualificationAlreadyExists($username, $degree_id);

        $sql = "INSERT INTO qualifications (username, degree_id, date_obtained) VALUES (?, ?, ?);";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("sis", $param_username, $param_degree, $param_date);
          $param_username = $username;
          $param_degree = $degree_id;
          $param_date = $date_obtained;

          if (!$stmt->execute()) {
            die("Database error: {$stmt->error}");
          }

          $stmt->close();
          respond(true, "UPDATED");
        } else {
          die("Database error: {$conn->error}");
        }
      }
    }

    /**
      * Processses the deletion of education history
      */
    function processDeleteEducationHistory() {
      global $conn;

      if (isset($_POST['remove_qualification_choice'])) {
        $value = $_POST['remove_qualification_choice'];

        if ($value != "no-choice") {
          $array = explode('-', $value);
          $id = $array[0];
          $username = $array[1];

          $sql = "DELETE FROM qualifications WHERE username = ? AND degree_id = ?;";

          if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_username, $param_degree);
            $param_username = $username;
            $param_degree = $id;

            if (!$stmt->execute()) {
              die("Database error: {$stmt->error}");
            }

            $stmt->close();
            respond(true, "UPDATED");
          } else {
            die("Database error: {$conn->error}");
          }
        }
      }
    }

    /**
      * Creates a new skill with the provided name and returns the id and adds the id to the provided response data object
      */
    function createSkill($skill_name) {
      global $conn;

      $sql = "INSERT INTO skills (name) VALUES (?);";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_name);
        $param_name = $skill_name;

        if (!$stmt->execute()) {
          die("Database error: {$stmt->error}");
        }

        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Checks if a skill with the provided name already exists and if not,
      * creates it. Returns the id of the skill to use.
      * Data is the response array to add the added skill to it
      */
    function getSkill($skill_name, &$data) {
      global $conn;

      $sql = "SELECT * FROM skills WHERE name = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_name);
        $param_name = $skill_name;

        if ($stmt->execute()) {
          $result = $stmt->get_result();

          $id = 0;
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $id = $row['skill_id'];
            }
          } else {
            $id = createSkill($skill_name);
          }

          $stmt->close();
          $data[$id] = $skill_name;
          return $id;
        } else {
          die("Database error: {$stmt->error}");
        }
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Returns true if the teacher skill already exists, false if not
      */
    function skillAlreadyExists($skill_id) {
      global $conn;
      global $username;

      $sql = "SELECT * FROM teacher_skills WHERE username = ? AND skill_id = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $param_username, $param_id);
        $param_username = $username;
        $param_id = $skill_id;

        if ($stmt->execute()) {
          $result = $stmt->get_result();
          $exists = $result->num_rows > 0;
          $stmt->close();

          return $exists;
        } else {
          die("Database error: {$stmt->error}");
        }
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Processes the addition of new skills
      */
    function processAddSkills() {
      global $conn;
      global $username;

      $response_data = array(); // this will hold the new ids and names of any created skills that did not already exist
      $skills_value = (isset($_POST['skills'])) ? $_POST['skills']:null;

      if ($skills_value == null) {
        respond(false, "Skills is a mandatory field");
      }

      $sql = "INSERT INTO teacher_skills (username, skill_id) VALUES (?, ?);";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $param_username, $param_id);
        $param_username = $username;
        $skills = explode(',', $skills_value);

        foreach ($skills as $key => $value) {
          $param_id = getSkill(trim($value), $response_data);

          if (!skillAlreadyExists($param_id)) {
            if (!$stmt->execute()) {
              die("Database error: {$stmt->error}");
            }
          } else {
            unset($response_data[$param_id]);
          }
        }

        $stmt->close();
        respondData(true, "UPDATED", $response_data);
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Processes the removal of a skill
      */
    function processSkillRemoval() {
      global $conn;
      global $username;

      $skill_ids = (isset($_POST['skills_choice'])) ? $_POST['skills_choice']:null;

      if ($skill_ids == null) {
        respond(false, "Remove Skill is a mandatory field");
      }

      $sql = "DELETE FROM teacher_skills WHERE username = ? AND skill_id = ?;";
      $data = array();

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $param_username, $param_id);
        $param_username = $username;

        foreach ($skill_ids as $key => $value) {
          $param_id = $value;
          if (!$stmt->execute()) {
            die("Database error: {$stmt->error}");
          }

          $data[] = $value;
        }

        $stmt->close();
        respondData(true, "UPDATED", $data);
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Checks if password matches
      */
    function checkPasswordMatch($password) {
      global $conn;
      global $username;

      $sql = "SELECT password FROM accounts WHERE username = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_user);
        $param_user = $username;

        if ($stmt->execute()) {
          $result = $stmt->get_result();

          $verified = false;
          if ($result->num_rows == 1) {
            while ($row = $result->fetch_assoc()) {
              $pass = $row['password'];
              $verified = password_verify($password, $pass) || $password == $pass;
            }
          }

          $stmt->close();

          return $verified;
        } else {
          die("Database error: {$stmt->error}");
        }
      } else {
        die("Database error: {$conn->error}");
      }
    }

    /**
      * Processes deleting the account
      */
    function processDeleteAccount() {
      global $username;
      global $conn;

      $password = (isset($_POST['delete_password'])) ? $_POST['delete_password']:null;

      if ($password == null) {
        respond(false, "Password is a mandatory field");
      }

      if (checkPasswordMatch($password)) {
        $sql = "DELETE FROM accounts WHERE username = ?";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_user);
          $param_user = $username;

          if (!$stmt->execute()) {
            die("Database error: {$stmt->error}");
          }

          $stmt->close();
          respond(true, "DELETED");
        } else {
          die("Database error: {$conn->error}");
        }
      } else {
        respond(false, "Password does not match your account password");
      }
    }

    /**
      * This function processes the request
      */
    function process() {
      global $edit_type;
      global $edit_form;

      if ($edit_type == TEACHER) {
        switch ($edit_form) {
          case UPDATE_PROFILE: processProfileUpdate();
                                break;
          case UPDATE_PASSWORD: processPasswordUpdate();
                                break;
          case JOIN_ORGANISATION: processJoinOrganisation();
                                  break;
          case EDUCATION_HISTORY: processEducationHistory();
                                  break;
          case DELETE_EDUCATION: processDeleteEducationHistory();
                                  break;
          case ADD_SKILLS: processAddSkills();
                            break;
          case REMOVE_SKILL: processSkillRemoval();
                                break;
          case DELETE_ACCOUNT: processDeleteAccount();
                                break;
          default: die("An invalid EDIT_FORM has been passed in: {$edit_form}");
        }
      } else if ($edit_type == ORGANISATION) {
        switch ($edit_form) {
          case UPDATE_PROFILE: processProfileUpdate();
                                break;
          case UPDATE_PASSWORD: processPasswordUpdate();
                                break;
          case DELETE_ACCOUNT: processDeleteAccount();
                                break;
          default: die("An invalid EDIT_FORM has been passed in: {$edit_form}");
        }
      } else {
        die("Unsupported EDIT_TYPE provided: {$edit_type}");
      }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      edit_profile_parsePOST();
      process();
    }
 ?>
