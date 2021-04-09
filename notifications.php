<!DOCTYPE HTML> 
<HTML>
    <HEAD>
         <title>Notifications TEST 2</title>
        <link href = "css/styles3.css" rel="stylesheet" type="text/css">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
        <script src="/test.js"></script>     
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
    <Body class=notificationPage> 

    <?php   
      ini_set('display_errors', '0');
      require "navbar.php";
      require "notifications_utils.php";
      require "database.php";
      generateNavBar(NOTIFICATIONS);
    ?>

    <div class = "wrapper">
        <P id=heading-2> Notifications <P>
        <a href = "#" class = "clear-all"> Clear All</a>

        <div class="notifications">
            <?php
                $sql = "select * from notifications;";
                $result = $conn->query($sql);
                while($row = $result->fetch_assoc()) {
                    $sender = $row['sender'];
                    $type = $row['type'];

                    $notificationType = "";
                    $button_text = "";

                    if($type == 'view'){
                        $notificationType = "Is viewing your profile";
                        $button_text = "Got to profile";
                    }else if($type == 'request'){
                        $notificationType = "Wants to connect with you";
                        $button_text = "Go to connection";
                    }else if($type == 'like'){
                        $notificationType = "Liked your post";
                        $button_text = "Go to post";
                    }else if($type == 'admin'){
                        $notificationType = "Welcome to Teachers Connect";
                        $button_text = "Contact Us";
                    }
                    
                    
                    echo '
                        <div class="notifications__item">
                            <div class="notifications__item__avatar">
                                <img src="images/Face.jpg" />
                                </div>
                                <div class="notifications__item__content">
                                <span class="notifications__item__title">'.$sender.'</span>
                                <span class="notifications__item__message">'.$notificationType.'</span>
                                <button class = "button-class" type="button">'.$button_text.'</button>
                                <a href="#" class="notifications__item.delete" >Delete</a>
                                <h6 class = "notifications__item.archive">2h</h6>
                            </div>
                    </div>';
                }
            ?>
        </div>
     </div>
    </Body>
</HTML>
