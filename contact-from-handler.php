<?php
    $name = $_Post['name'];
    $email = $_Post['email'];
    $message = $_Post['message'];

    $subject = "Teachers Connect Query";
    $content = "User Name: $name
                ./n.".
                "User Email: $email
                ./n".
                "Message: $message
                ./n"
                ;

    $send_to = "18266401@studentmail.ul.ie";

    if( mail($send_to, $subject, $content)!==true)
    {
        die('Fail to send');
    }else{
        echo "Success";
        header("Location: landingpage.php");
    }
?> 