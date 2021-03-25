<?php
    $username = "";
    $password = "";

    //get values passed into form
    if(isset($_POST["submit"])){
        if(empty($_POST["username"]) || empty($_POST["password"])){
            $error = "username or password invalid";
        }
        else{
            $username = $_POST["username"];
            $password = $_POST["password"];
        }
    }

    //connection
    $dbservername = "localhost";
    $dbusername = "id16326203_admin";
    $dbpassword = "CS4116_group08";
    $dbname = "id16326203_db";
    
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

    //check connection
    if($conn->connect_error){
        die("Connection failed Ayoub, ".$conn->connect_error);
    }else{
        echo "Connected succesffully";
    }

    //query db
    $sql = "SELECT * FROM accounts WHERE username = \"{$username}\" AND password = \"{$password}\";";
    $result = $conn->query($sql);

    if($row["username"] == $username && $row["password"] == $password){
        echo "Logged in account: ".$row['username'];
    }else {
        echo 'Log in failed :(';
    }

    $conn->close();

?>