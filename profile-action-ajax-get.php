<?php
  require "database.php";
  require "constants.php";

  $sender = "";
  $destination = "";
  $action = "";
  $action_param = "";

  /**
    * Throw error
    */
  function throwError() {
    die("Script called with missing variables");
  }

  /**
    * Respond to the request
    */
  function respond($success, $message) {
    http_response_code(($success) ? 200:401);
    $response = [
      'success' => $success,
      'message' => $message
    ];
    echo json_encode($response);
  }

  /**
    * Parses the URL for any GET parameters
    */
  function profile_action_parseURL() {
    global $sender;
    global $destination;
    global $action;
    global $action_param;
    global $return_url;

    $parsed_url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
    $params = array();
    parse_str($parsed_url, $params);

    if (isset($params[ACTION])) {
      $action = $params[ACTION];
    } else {
      throwError();
    }

    if (isset($params[ACTION_PARAM])) {
      $action_param = $params[ACTION_PARAM];
    }

    if (isset($params[SENDER])) {
      $sender = $params[SENDER];
    } else {
      throwError();
    }

    if (isset($params[DESTINATION])) {
      $destination = $params[DESTINATION];
    } else {
      throwError();
    }
  }

  /**
    * Checks if the users are already connected or not
    */
  function isConnected() {
    global $sender;
    global $destination;
    global $conn;

    $sql = "SELECT * FROM connections WHERE (destination = ? AND sender = ?) OR (destination = ? AND sender = ?) LIMIT 1;";

    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("ssss", $param_dest1, $param_send1, $param_dest2, $param_send2);
      $param_dest1 = $destination;
      $param_send1 = $sender;
      $param_dest2 = $sender;
      $param_send2 = $destination;

      $connected = false;
      if ($stmt->execute()) {
        $stmt->store_result();
        $connected = $stmt->num_rows == 1;
      } else {
        die("Database error: {$stmt->error}");
      }

      $stmt->close();
      return $connected;
    } else {
      die("Database error: {$conn->error}");
    }

    return false;
  }

  /**
    * Delete a connection
    */
  function deleteConnection() {
    global $conn;
    global $destination;
    global $sender;

    $sql = "DELETE FROM connections WHERE destination = ? AND sender = ?;";

    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("ss", $param_destination, $param_sender);
      $param_destination = $destination;
      $param_sender = $sender;

      if (!$stmt->execute()) {
        die("Database error: {$stmt->error}");
      }

      $stmt->close();
      respond(true, "REMOVED");
    } else {
      die("Database error: {$conn->error}");
    }
  }

  /**
    * Accept the provided connection
    */
  function acceptConnection() {
    if (isConnected()) {
      global $conn;
      global $destination;
      global $sender;

      $sql = "UPDATE connections SET status='accepted' WHERE destination = ? AND sender = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $param_destination, $param_sender);
        $param_destination = $destination;
        $param_sender = $sender;

        if (!$stmt->execute()) {
          die("Database error: {$stmt->error}");
        }

        $stmt->close();
        respond(true, "ACCEPTED");
        exit;
      } else {
        die("Database error: {$conn->error}");
      }
    }

    respond(false, "NOT CONNECTED");
  }

  /**
    * Create the connection as a request
    */
  function createConnection() {
    if (!isConnected()) {
      global $conn;
      global $destination;
      global $sender;

      $sql = "INSERT INTO connections (destination, sender, status) VALUES (?, ?, 'pending');";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $param_destination, $param_sender);
        $param_destination = $destination;
        $param_sender = $sender;

        if (!$stmt->execute()) {
          die("Database error: {$stmt->error}");
        }

        $stmt->close();
        respond(true, "PENDING");
        exit;
      } else {
        die("Database error: {$conn->error}");
      }
    }

    respond(false, "ALREADY CONNECTED");
  }

  /**
    * Processes the connection
    */
  function processConnection() {
    global $action_param;

    if ($action_param == REMOVE) {
      deleteConnection();
    } else if ($action_param == ADD) {
      createConnection();
    } else if ($action_param == ACCEPT) {
      acceptConnection();
    } else {
      throwError();
    }
  }

  /**
    * Deletes the block of this teacher
    */
  function deleteBlock() {
    global $conn;
    global $destination;
    global $sender;

    $sql = "DELETE FROM blocked_teachers WHERE blocked_teacher = ? AND blocker = ?;";

    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("ss", $param_destination, $param_sender);
      $param_destination = $destination;
      $param_sender = $sender;

      if (!$stmt->execute()) {
        die("Database error: {$stmt->error}");
      }

      $stmt->close();
      respond(true, "REMOVED");
    } else {
      die("Database error: {$conn->error}");
    }
  }

  /**
    * Adds the block to the databas
    */
  function addBlock() {
    global $conn;
    global $destination;
    global $sender;

    $sql = "INSERT INTO blocked_teachers (blocked_teacher, blocker) VALUES (?, ?);";

    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("ss", $param_destination, $param_sender);
      $param_destination = $destination;
      $param_sender = $sender;

      if (!$stmt->execute()) {
        die("Database error: {$stmt->error}");
      }

      $stmt->close();
      respond(true, "BLOCKED");
    } else {
      die("Database error: {$conn->error}");
    }
  }

  /**
    * Process blocking of a user
    */
  function processBlock() {
    global $action_param;

    if ($action_param == REMOVE) {
      deleteBlock();
    } else if ($action_param == ADD || empty($action_param)) {
      addBlock();
    }
  }

  /**
    * Removes the follow
    */
  function removeFollow() {
    global $conn;
    global $destination;
    global $sender;

    $sql = "DELETE FROM followed_organisations WHERE teacher_username = ? AND organisation_id = ?;";

    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("si", $param_user, $param_id);
      $param_user = $sender;
      $param_id = $destination;

      if (!$stmt->execute()) {
        die("Database error: {$stmt->error}");
      }

      $stmt->close();
      respond(true, "REMOVED");
    } else {
      die("Database error: {$conn->error}");
    }
  }

  /**
    * Adds a new follow
    */
  function addFollow() {
    global $conn;
    global $destination;
    global $sender;

    $sql = "INSERT INTO followed_organisations (teacher_username, organisation_id) VALUES (?, ?);";

    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("si", $param_user, $param_id);
      $param_user = $sender;
      $param_id = $destination;

      if (!$stmt->execute()) {
        die("Database error: {$stmt->error}");
      }

      $stmt->close();
      respond(true, "FOLLOWED");
    } else {
      die("Database error: {$conn->error}");
    }
  }

  /**
    * Proces the following of an organisation
    */
  function processFollow() {
    global $action_param;
    if ($action_param == REMOVE) {
      removeFollow();
    } else if ($action_param == ADD || empty($action_param)) {
      addFollow();
    }
  }

  /**
    * Process the requested action
    */
  function process() {
    global $action;

    if ($action == CONNECT) {
      processConnection();
    } else if ($action == BLOCK) {
      processBlock();
    } else if ($action == FOLLOW) {
      processFollow();
    }
  }

  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    profile_action_parseURL();
    process();
  }
 ?>
