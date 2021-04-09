<!DOCTYPE HTML>
<HTML>
    <HEAD>
         <title>Notifications</title>
        <link href = "css/style3.css" rel="stylesheet" type="text/css">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
        <!--<script src="/test.js"></script>-->
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


      <!--  <script>
            function loadNotifcation(type, sender){

                    const type = type;
                    const sender = sender;

                    const x = ""
                    const y = "";

                    if(type == 'view'){
                        x = ViewNotification.messageForNotification;
                        y = "Got to profile"
                    }else if(type == 'request')[
                        x = ConnectionNotification.messageForNotification;
                        y = "Go to connection"
                    ]else if(type == 'like'){
                        x = LikeNotification.messageForNotification;
                        y = "Go to post"
                    }


                    const divCard = document.createElement('div');
                    divCard.classList('notification_item');

                    const divImg = document.createElement('img');
                    divImg.classList('notifications__item__avatar');
                    divImg.src = '/images/Face.jpg';

                    const divContent = document.createElement('div');
                    divContent.classList('notifications__item__content');

                    const divTitle = document.createElement('span');
                    const divTitleText = document.createTextNode($sender);
                    divTitle.appendChild(divTitleText);
                    divTitle.classList('notifications__item__title');

                    const divMessage = document.createElement('span');
                    const divMessageText = document.createTextNode(x);
                    divTitle.appendChild(divMessageText);
                    divTitle.classList('notifications__item__message');

                    const divButton = document.createElement('button');
                    const divButtonText = document.createTextNode(y)
                    divButton.appendChild(divButtonText);
                    divButton.classList('button-class');
                }
        </script>-->

        <div class="notifications">
            <?php
                /**
                  * Retrieve the profile photo for the user
                  */
                function getSenderProfilePhoto($sender) {
                  global $conn;

                  $user_type = getUserType($sender);

                  $table = null;
                  if ($user_type == TEACHER) {
                    $table = "teachers";
                  } else if ($user_type == ORGANISATION) {
                    $table = "organisations";
                  }

                  if ($table != null) {
                    $sql = "SELECT profile_photo FROM {$table} WHERE username = ?;";

                    if ($stmt = $conn->prepare($sql)) {
                      $stmt->bind_param("s", $param_user);
                      $param_user = $sender;

                      $profile_photo = ($user_type == TEACHER) ? DEFAULT_TEACHER_PROFILE_PIC:DEFAULT_ORG_PROFILE_PIC;
                      if ($stmt->execute()) {
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                          $photo = $row['profile_photo'];

                          if ($photo != null) {
                            $profile_photo = $photo;
                          }
                        }
                      } else {
                        die("Database Error: {$stmt->error}");
                      }

                      $stmt->close();
                      return $profile_photo;
                    } else {
                      die("Database Error: {$conn->error}");
                    }
                  }
                }

                $sql = "SELECT * FROM notifications WHERE username = ?;";

                if ($stmt = $conn->prepare($sql)) {
                  $stmt->bind_param("s", $param_username);
                  $param_username = $username;

                  if ($stmt->execute()) {
                    $result = $stmt->get_result();

                    while($row = $result->fetch_assoc()) {
                        $sender = $row['sender'];
                        $type = $row['type'];
                        $link = $row['target_link'];

                        if ($link == null || empty($link)) {
                          $link = "#";
                        }

                        $notificationType = "";
                        $button_text = "";

                        if($type == 'view'){
                            $notificationType = "Is viewing your profile";
                            $button_text = "Go to profile";
                        }else if($type == 'request'){
                            $notificationType = "Wants to connect with you";
                            $button_text = "Go to connection";
                        }else if($type == 'like'){
                            $notificationType = "Liked your post";
                            $button_text = "Go to post";
                        }

                        $profile_photo = getSenderProfilePhoto($sender);

                        echo "
                            <div class=\"notifications__item\">
                                <div class=\"notifications__item__avatar\">
                                    <img src=\"{$profile_photo}\" />
                                    </div>
                                    <div class=\"notifications__item__content\">
                                    <span class=\"notifications__item__title\">{$sender}</span>
                                    <span class=\"notifications__item__message\">{$notificationType}</span>
                                    <a class = \"btn btn-primary\" href=\"{$link}\">{$button_text}</a>
                                    <a href=\"#\" class=\"deleteButton\" >Delete</a>
                                    <h6 class = \"time\">2h</h6>
                                </div>
                        </div>";
                    }
                  } else {
                    echo "An error occurred: {$stmt->error}";
                  }
                } else {
                  echo "An error occurred: {$conn->error}";
                }
            ?>
        </div>
     </div>
    </Body>
</HTML>
