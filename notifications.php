<!DOCTYPE html>
<html>
    <head>
        <title>Notifications</title>
        <link href = "css/notifications.css" rel="stylesheet" type="text/css">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
        <!--<script src="/test.js"></script>-->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body class="notificationPage">

       <?php
        error_reporting(0);
        ini_set('display_errors', 0);
        require "navbar.php";
        require "notifications_utils.php";
        require "database.php";
        generateNavBar(NOTIFICATIONS);
      ?>

      <div class = "wrapper">
          <P id="heading-2"> Notifications <P>
          <a href = "deleteall.php" class = "clear-all"> Clear All</a>

          <div class="notification-tray">
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

                    function format_time($timestamp)  
                    {  
                         $time_ago = strtotime($timestamp);  
                         $current_time = time();  
                         $time_difference = $current_time - $time_ago;  
                         $seconds = $time_difference;  
                         $minutes = round($seconds / 60 );
                         $hours   = round($seconds / 3600);
                         $days    = round($seconds / 86400); 
                         $weeks   = round($seconds / 604800);
                         $months  = round($seconds / 2629440);  
                         $years   = round($seconds / 31553280);
                         if($seconds <= 60)  
                         {  
                        return "Just Now";  
                      }  
                         else if($minutes <=60)  
                         {  
                        if($minutes==1)  
                              {  
                          return "one minute ago";  
                        }  
                        else  
                              {  
                          return "$minutes minutes ago";  
                        }  
                      }  
                         else if($hours <=24)  
                         {  
                        if($hours==1)  
                              {  
                          return "an hour ago";  
                        }  
                              else  
                              {  
                          return "$hours hrs ago";  
                        }  
                      }  
                         else if($days <= 7)  
                         {  
                        if($days==1)  
                              {  
                          return "yesterday";  
                        }  
                              else  
                              {  
                          return "$days days ago";  
                        }  
                      }  
                         else if($weeks <= 4.3)
                         {  
                        if($weeks==1)  
                              {  
                          return "a week ago";  
                        }  
                              else  
                              {  
                          return "$weeks weeks ago";  
                        }  
                      }  
                          else if($months <=12)  
                         {  
                        if($months==1)  
                              {  
                          return "a month ago";  
                        }  
                              else  
                              {  
                          return "$months months ago";  
                        }  
                      }  
                         else  
                         {  
                        if($years==1)  
                              {  
                          return "one year ago";  
                        }  
                              else  
                              {  
                          return "$years years ago";  
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
                          $time = format_time($row['created_at']);
                          $id = $row['id'];
                          $username = $row['username'];

                          if ($link == null || empty($link)) {
                            $link = "#";
                          }

                          $notificationType = "";
                          $button_text = "";

                          if($type == 'view'){
                            $notificationType = "Is viewing your profile";
                            $button_text = "Go to profile";
                          } else if($type == 'request'){
                            $notificationType = "Wants to connect with you";
                            $button_text = "Go to connection";
                          } else if($type == 'like'){
                            $notificationType = "Liked your post";
                            $button_text = "Go to post";
                          } else if($type == 'admin'){
                            $notificationType = "Welcome to Teachers Connect!";
                            $button_text = "Contact Us";
                            $link = "./landingpage.php#landing-contact";
                          }

                          $profile_photo = getSenderProfilePhoto($sender);
                          $profile_photo = ($profile_photo == null || empty($profile_photo)) ? "images/logo.png":$profile_photo;

                          echo "
                              <div class=\"notifications__item\">
                                  <div class=\"notifications__item__avatar\">
                                      <img src=\"{$profile_photo}\" />
                                      </div>
                                      <div class=\"notifications__item__content\">
                                      <span class=\"notifications__item__title\">{$sender}</span>
                                      <span class=\"notifications__item__message\">{$notificationType}</span>
                                      <a class = \"btn btn-primary\" href=\"{$link}\">{$button_text}</a>
                                      <div class=\"side-group\">
                                          <h6 class = \"time\"> $time</h6>
                                          <a href=\"deleteitem.php?id=$id\">Delete</a>
                                        </div>
                                  </div>
                          </div>";
                          // echo "<a href=\"deleteall.php?username=$username\">Clear</a>";

                          // echo "<a href=\"deleteall.php?username=$username\">Delete</a>";
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
    </body>
</html>
