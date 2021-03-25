<?php
  session_start();

  require "constants.php";

  function goToLogin() {
    header("Location: login.php");
    exit;
  }

  if (isset($_SESSION[LOGGED_IN]) && $_SESSION[LOGGED_IN] == true) {
    if (!isset($_SESSION[USERNAME]) || !isset($_SESSION[USER_TYPE])) {
       goToLogin();
     }

     header("Location: feed.php");
     exit;
   } else {
     goToLogin();
   }
 ?>
