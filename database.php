<?php
  $servername = "localhost";
  $username = "id16326203_admin";
  $password = "CS4116_group08";
  $db = "id16326203_db";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $db);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
?>
