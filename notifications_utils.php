<?php
  require_once "database.php";

  abstract class Notification {
            public $sender;
            public $receiver;
            public $type;
            public $viewed;
            public $target_link;
            public $created_at;

            public function messageForNotification(Notification $notification) : string{
            }

            //_SET Methods
            function setSender($sender){
                $this->sender = $sender;
            }

            function setCreated_at($created_at){
                $this->created_at = $created_at;
            }

            function setTarget_link($target_link){
                $this->target_link = $target_link;
            }

            function setViewed($viewed){
                $this->viewed = $viewed;
            }

            function setType($type){
                $this->type = $type;
            }

            function setReceiver($receiver){
                $this->receiver = $receiver;
            }

            //_Notifications_Constructor
            function __construct($sender, $receiver, $type, $viewed, $target_link, $created_at) {
                $this->setSender($sender);
                $this->setReceiver($receiver);
                $this->setType($type);
                $this->setViewed($viewed);
                $this->setTarget_link($target_link);
                $this->setCreated_at($created_at);
              }


            function message() : string{
                return $this->notificationMessage($this);
            }

            //_GET Methods
            function getSender() : string{
                return $this->sender;
            }

            function getReceiver() : string{
                return $this->receiver;
            }

            function getType() : string{
                return $this->type;
            }

            function getViewed() : bool{
                return $this->viewed;
            }

            function getTarget_link() : string{
                return $this->target_link;
            }

            function getCreated_at() { //??
                return $this->created_at;
            }
        }

        class LikeNotification extends Notification{
            function __construct($sender, $receiver, $viewed, $target_link, $created_at) {
              parent::__construct($sender, $receiver, 'like', $viewed, $target_link, $created_at);
            }

            public function messageForNotification(Notification $notification) : string{
                return $this->sender->getSender() . 'has liked your post:';
            }
        }

        class ConnectionNotification extends Notification{
          function __construct($sender, $receiver, $viewed, $target_link, $created_at) {
            parent::__construct($sender, $receiver, 'request', $viewed, $target_link, $created_at);
          }

            public function messageForNotification(Notification $notification) : string{
                return $this->sender->getSender() . 'wants to connect with you';
            }
        }

        class ViewNotification extends Notification{
          function __construct($sender, $receiver, $viewed, $target_link, $created_at) {
            parent::__construct($sender, $receiver, 'view', $viewed, $target_link, $created_at);
          }

            public function messageForNotification(Notification $notification) : string{
                return $this->sender->getSender() . 'is viewing your profile';
            }
        }

        class AdminNotification extends Notification{
          function __construct($sender, $receiver, $viewed, $target_link, $created_at) {
            parent::__construct($sender, $receiver, 'admin', $viewed, $target_link, $created_at);
          }

          public function messageForNotification(Notification $notification) : string{
              return "Welcome to Teachers Connect!";
          }
      }

        function addNotification(Notification $notification){
          global $conn;

          $sql = "INSERT INTO notifications (username, sender, type, target_link) VALUES (?, ?, ?, ?);";

          if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $param_username, $param_sender, $param_type, $param_target);
            $param_username = $notification->getReceiver();
            $param_sender = $notification->getSender();
            $param_type = $notification->getType();
            $param_target = $notification->getTarget_link();

            if (!$stmt->execute()) {
              die("Database error: {$stmt->error}");
            }

            $stmt->close();
          } else {
            die("Database error: {$conn->error}");
          }
        }

        function AddAdminNotification(Notification $notification){
          global $conn;

          $sql = "INSERT INTO notifications (username, sender, type, target_link) VALUES (?, ?, 'admin', ?);";

          if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $param_username, $param_sender, $param_target);
            $param_username = $notification->getReceiver();
            $param_sender = "Teachers Connect Bot";
            $param_target = $notification->getTarget_link();

            if (!$stmt->execute()) {
              die("Database error: {$stmt->error}");
            }

            $stmt->close();
          } else {
            die("Database error: {$conn->error}");
          }
        }
 ?>
