<?php 
    include "database.php";

    $username = $_GET['username'];

    $deleteallquery = "DELETE * FROM `notifications` WHERE username = $username";

    $query = mysqli_query($conn, $deleteallquery);

    header('location: notifications.php')

?>