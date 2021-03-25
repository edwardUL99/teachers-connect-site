<?php
  $servername = "localhost";
  $dbusername = "id16326203_admin";
  $password = "CS4116_group08";
  $db = "id16326203_db";

  // Create connection
  $conn = new mysqli($servername, $dbusername, $password, $db);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
?>
