<?php
  session_start();

  require "constants.php";

  function goToLogin() {
    header("Location: landingpage.php");
    exit;
  }

   if (isset($_SESSION[LOGGED_IN]) && $_SESSION[LOGGED_IN] == true) {
     if (!isset($_SESSION[USERNAME]) || !isset($_SESSION[USER_TYPE])) {
        goToLogin();
      }

      header("Location: feed.php");
      exit;
    } else {
      if (isset($_COOKIE[USERNAME]) && isset($_COOKIE[USER_TYPE])) {
        $username = $_SESSION[USERNAME] = $_COOKIE[USERNAME];
        $user_type = $_SESSION[USER_TYPE] = $_COOKIE[USER_TYPE];
        $_SESSION[LOGGED_IN] = true;

        header("Location: feed.php");
        exit;
      } else {
        goToLogin();
      }
    }
 ?>
