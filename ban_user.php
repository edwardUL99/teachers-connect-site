<?php
  require "database.php";

  /**
    * This script contains utilities for banning/blacklisting users
    */
    define('BAN', 'ban');
    define('UNBAN', 'unban');
    define('BLACKLIST', 'blacklist');
    define('UNBLACKLIST', 'unblacklist');

    $action = "";
    $username = "";
    $email = ""; // only valid for blacklist or unblacklist
    $admin = "";
    $reason = "";
    $date_to = "";
    $time_to = "";
    $return_url = "";

    /**
      * Return to the Return URL
      */
    function returnToURL() {
      global $return_url;
      header("Location: ".$return_url);
    }

    /**
      * Ban the user
      */
    function banUser() {
      global $username;
      global $admin;
      global $reason;
      global $date_to;
      global $time_to;
      global $conn;

      $sql = "INSERT INTO banned_users (username, banned_by, reason, date_to) VALUES (?, ?, ?, ?);";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $param_user, $param_admin, $param_reason, $param_date);
        $param_user = $username;
        $param_admin = $admin;
        $param_reason = $reason;
        $param_date = "".$date_to." ".$time_to;

        if (!$stmt->execute()) {
          die("Database Error: {$stmt->error}");
        }

        $stmt->close();
        returnToURL();
      } else {
        die("Database Error: {$conn->error}");
      }
    }

    /**
      * Remove the ban on the user
      */
    function unBanUser() {
      global $conn;
      global $username;

      $sql = "DELETE FROM banned_users WHERE username = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_user);
        $param_user = $username;

        if (!$stmt->execute()) {
          die("Database Error: {$stmt->error}");
        }

        $stmt->close();
        returnToURL();
      } else {
        die("Database Error: {$conn->error}");
      }
    }

    /**
      * Get the user's email address
      */
    function getUserEmail() {
      global $conn;
      global $username;

      $sql = "SELECT email FROM accounts WHERE username = ?;";

      if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_user);
        $param_user = $username;

        if ($stmt->execute()) {
          $result = $stmt->get_result();

          if ($result->num_rows == 1) {
            while ($row = $result->fetch_assoc()) {
              return $row['email'];
            }
          } else {
            return null;
          }
        } else {
          die("Database Error: {$stmt->error}");
        }

        $stmt->close();
      } else {
        die("Database Error: {$conn->error}");
      }
    }

    /**
      * Blacklist the user's email address
      */
    function blacklistUser() {
      global $email;
      global $username;
      global $conn;

      $email = (empty($email)) ? getUserEmail():$email;

      if ($email != null) {
        $sql = "INSERT INTO email_blacklist (email) VALUES (?);";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_email);
          $param_email = $email;

          if (!$stmt->execute()) {
            die("Database Error: {$stmt->error}");
          }

          $stmt->close();
          returnToURL();
        } else {
          die("Database Error: {$conn->error}");
        }
      } else {
        returnToURL();
      }
    }

    /**
      * Unblacklist the user's email address
      */
    function unBlacklistUser() {
      global $email;
      global $username;
      global $conn;

      $email = (empty($email)) ? getUserEmail():$email;

      if ($email != null) {
        $sql = "DELETE FROM email_blacklist WHERE email = ?;";

        if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("s", $param_email);
          $param_email = $email;

          if (!$stmt->execute()) {
            die("Database Error: {$stmt->error}");
          }

          $stmt->close();
          returnToURL();
        } else {
          die("Database Error: {$conn->error}");
        }
      } else {
        returnToURL();
      }
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
      $url = $_SERVER["REQUEST_URI"];
      $parsed_url = parse_url($url, PHP_URL_QUERY);
      $array = array();
      parse_str(html_entity_decode($parsed_url), $array);
      $_POST = $array;
    }

    if (isset($_POST['action'])) {
      $action = $_POST['action'];
    } else {
      die("You need to provide an action to this script");
    }

    if (isset($_POST['username'])) {
      $username = $_POST['username'];
    } else {
      if ($action == BLACKLIST || $action == UNBLACKLIST) {
        if (isset($_POST['email'])) {
          $email = $_POST['email'];
        } else {
          die("You need to provide the email of the user this script is being called for");
        }
      } else {
        die("You need to provide the username of the user this script is being called for");
      }
    }

    if (isset($_POST['admin'])) {
      $admin = $_POST['admin'];
    } else {
      die("You need to provide the username of the admin calling this script");
    }

    if (isset($_POST['return_url'])) {
      $return_url = $_POST['return_url'];
    } else {
      die("You need to provide a URL to return to");
    }

    if ($action == BAN) {
      if (isset($_POST['reason'])) {
        $reason = $_POST['reason'];
      }

      if (isset($_POST['date_to'])) {
        $date_to = $_POST['date_to'];
      } else {
        die("You need to provide the date till which this user is banned");
      }

      if (isset($_POST['time_to'])) {
        $time_to = $_POST['time_to'];
      } else {
        die("You need to provide the time till which this user is banned");
      }

      banUser();
    } else if ($action == UNBAN) {
      unBanUser();
    } else if ($action == BLACKLIST) {
      blacklistUser();
    } else if ($action == UNBLACKLIST) {
      unBlacklistUser();
    } else {
      die("Unknown action provided: {$action}");
    }
 ?>
