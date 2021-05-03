<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
    	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<Title>Home</Title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
        <link type="text/css" rel="stylesheet" href="css/feed.css">
				<link type="text/css" rel="stylesheet" href="css/styles.css">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

	<body>

        <?php
            require "database.php";
            require "error.php";
            require "navbar.php";
			require "teacher.php";
			require "organisation.php";

						$teacher = null;
						$organisation = null;
						$profile_photo = null;

						if ($user_type == TEACHER) {
							$sql = "SELECT * FROM teachers WHERE username = ?;";

							if ($stmt = $conn->prepare($sql)) {
								$stmt->bind_param("s", $param_user);
								$param_user = $username;

								if ($stmt->execute()) {
									$result = $stmt->get_result();
									$row = $result->fetch_assoc();

									$teacher = new Teacher($username, $row['first_name'], $row['last_name'], $row['headline'],
									$row['about'], $row['location'], $row['profile_photo']);
								} else {
									die("Database Error: {$stmt->error}");
								}

								$stmt->close();
							} else {
								die("Database Error: {$conn->error}");
							}
						} else if ($user_type == ORGANISATION) {
							$sql = "SELECT * FROM organisations WHERE username = ?;";

							if ($stmt = $conn->prepare($sql)) {
								$stmt->bind_param("s", $param_user);
								$param_user = $username;

								if ($stmt->execute()) {
									$result = $stmt->get_result();
									$row = $result->fetch_assoc();

									$organisation = new Organisation($row['organisation_id'], $username,
									$row['name'], $row['headline'], $row['about'], $row['location'], $row['profile_photo']);
								} else {
									die("Database Error: {$stmt->error}");
								}

								$stmt->close();
							} else {
								die("Database Error: {$conn->error}");
							}
						}

						$default = null;
						if ($teacher != null) {
							$profile_photo = $teacher->profile_photo();
							$default = DEFAULT_TEACHER_PROFILE_PIC;
						} else if ($organisation != null) {
							$profile_photo = $organisation->profile_photo();
							$default = DEFAULT_ORG_PROFILE_PIC;
						} else {
							$default = "images/logo.png";
						}

						$profile_photo = ($profile_photo == null) ? $default:$profile_photo;

						$name = null;

						if ($teacher != null) {
							$name = $teacher->firstName() . " " . $teacher->lastName();
						} else if ($organisation != null) {
							$name = $organisation->name();
						}

						/**
							* Gets the count of unread notifications
							*/
						function getNotificationsCount() {
							global $conn;
							global $username;

							$sql = "SELECT COUNT(*) AS 'num_notifications' FROM notifications WHERE username = ? AND viewed = 0;";

							if ($stmt = $conn->prepare($sql)) {
								$stmt->bind_param("s", $param_username);
								$param_username = $username;

								$num_notifications = 0;
								if ($stmt->execute()) {
									$result = $stmt->get_result();
									$num_notifications = $result->fetch_assoc()['num_notifications'];
								}

								$stmt->close();
								return $num_notifications;
							} else {
								return 0;
							}
						}
        ?>

        <?php
            generateNavBar(HOME);
        ?>

        <div class="container">
			<div class="row">
				<div class="<?php echo ($user_type != ADMIN) ? 'col-3':'d-none'; ?>" id="profile-home-panel">
					<div class="card" style="margin-top:20px;">
                        <img class="side-bar-profile-pic rounded-circle m-auto" src="<?php echo $profile_photo; ?>" alt="Profile Pic">
						<div class="card-body text-center">
							<h4 class="card-title">
							    <?php
							   		echo $name;
                    			?>
							</h4>
							<p class="card-text" style="font-size:14px;">

								<?php
									if($user_type == TEACHER){
										$tag_query = mysqli_query($conn, "select * from teachers where username = '" . $_SESSION['username'] . "';");
										while($connection_row = mysqli_fetch_array($tag_query)){
											echo $connection_row['about'];
										}
									} else if($user_type == ORGANISATION){
										$tag_query = mysqli_query($conn, "select * from organisations where username = '" . $_SESSION['username'] . "';");
										while($connection_row = mysqli_fetch_array($tag_query)){
											echo $connection_row['about'];
										}
									}
								?>

							</p>
							<?php
								$noti_count = getNotificationsCount();
								if($noti_count == 0){
									echo '<button type="button" class="btn btn-primary" onclick="window.location.href="notifications.php";">
										Notifications <span class="badge bg-secondary">'. $noti_count .'</span>
									</button>';
								} else {
									echo '<button type="button" class="btn btn-success" onclick="window.location.href="notifications.php";">
										Notifications <span class="badge bg-secondary"> '. $noti_count .'</span>
									</button>';
								}

							?>
						</div>
					</div>

					<div class="card">
						<div class="tags" style="text-align: center;">
							<h3><b><u>Tags</b></u></h3>
							<?php
								$tag_query = mysqli_query($conn, "select teacher_interests.username, tags.name from teacher_interests inner join tags on teacher_interests.tag_id = tags.tag_id where username = '" . $_SESSION['username'] . "';");
								while($connection_row = mysqli_fetch_array($tag_query)){
									echo '<p>' . $connection_row['name'] . '</p>';
								}
							?>
						</div>
					</div>

					<div class="card" style="margin-top:20px;">
						<button type="button" class="btn btn-primary btn-sm" style="margin:20px" onclick="window.location.href='edit_tags.php';">
							Add tags to follow
						</button>
					</div>

            	</div>

            	<div class="<?php echo ($user_type != ADMIN) ? 'col-9':'col'; ?>" id="home-feed">
					<div class="card" style="margin-top:20px;">
						<div class="form-group" style="margin:10px;">
							<form name="form" action="" method="post">
								<textarea class="form-control" name="content" id="exampleFormControlTextarea1" rows="3" placeholder="Share your thoughts!" style="margin-bottom:10px;"></textarea>
								<textarea class="form-control" name="tags" id="exampleFormControlTextarea1" rows="1" placeholder="Add tags to your post!" style="resize: none;"></textarea>
								<p style="font-size:12px;">Enter skills to add in a comma-separated (,) list</p>
								<div class="card-body text-center">
									<button type="submit" class="btn btn-primary">Post</button>
								</div>
							</form>
							<?php
								if($_SERVER["REQUEST_METHOD"] == "POST"){
									if(isset($_POST['content']) and $_POST['tags'] != ''){
										$content = $_POST['content'];
										$tags = $_POST['tags'];
										$username = $_SESSION['username'];

										$sql = "INSERT INTO posts (`username`, `content`) VALUES ('{$username}', '{$content}');";

										if ($conn->query($sql) === FALSE) {
											echo "Error uploading post, try again";
										} else {
											$last_id = $conn->insert_id;
											$myArray = explode(',', $tags);
											foreach ($myArray as $value) {
												$query9 = mysqli_query($conn, "select * from tags where name = '$value'");
												while($row = mysqli_fetch_array($query9)){
													$tag_id = $row['tag_id'];
												}

												if(isset($tag_id)){
													$sql2 = "INSERT INTO post_tags (post_id, tag_id)
													VALUES ('".$last_id."', '".$tag_id."')";
													$conn->query($sql2);
													unset($tag_id);

												} else {
													$sql3 = "INSERT INTO tags (name)
													VALUES ('".$value."')";
													$conn->query($sql3);
													$last_id2 = $conn->insert_id;
													$sql4 = "INSERT INTO post_tags (post_id, tag_id)
													VALUES ('".$last_id."', '".$last_id2."')";
													$conn->query($sql4);
												}
											}
										}
									}
								}
							?>
						</div>
					</div>


					<?php
						//this array is used to see teachers the user is friends with, companies they follow
						function getAccountType(string $account_username, $conn) {
							$account_query = mysqli_query($conn, "select * from accounts where username = '". $account_username ."'");
							if($account_row = mysqli_fetch_array($account_query)){
								return $account_row['type'];
							}
							return null;
						}

						function getOrgId(string $org_username, $conn){
							$org_query = mysqli_query($conn, "select * from organisations where username = '". $org_username ."'");
							if($org_row = mysqli_fetch_array($org_query)){
								return $org_row['organisation_id'];
							}
							return null;
						}

						function addToPostArray($param_query, string $post_column, $post_list){
							//populate post_list from sql query
							while($connection_row = mysqli_fetch_array($param_query)){
								array_push($post_list, $connection_row[$post_column]);
							}
							return $post_list;
						}

						function deletePost(int $post_id){
							global $conn;
							print_r($post_id);
							$delete_post_query = mysqli_query($conn, "delete from posts where post_id='" . $post_id . "';");
						}

						$post_list = [];
						if($user_type == TEACHER){
							array_push($post_list, $_SESSION['username']);
						} else if($user_type == ORGANISATION){
							array_push($post_list, getOrgId($_SESSION['username'], $conn));
						}

						$profile_photo = null;
						$content = null;
						$post_name = null;

						$sql_friend_check_sender = mysqli_query($conn, "select * from connections where sender = '" . $_SESSION['username'] . "' and status = 'accepted';");
						$post_list = addToPostArray($sql_friend_check_sender, 'destination', $post_list);

						$sql_friend_check_des = mysqli_query($conn, "select * from connections where destination = '" . $_SESSION['username'] . "' and status = 'accepted';");
						$post_list = addToPostArray($sql_friend_check_des, 'sender', $post_list);

						$sql_org_check = mysqli_query($conn, "select * from followed_organisations where teacher_username = '" . $_SESSION['username'] . "';");
						$post_list = addToPostArray($sql_org_check, 'organisation_id', $post_list);

						$query = mysqli_query($conn, "select * from posts order by created_at desc");
						while($row = mysqli_fetch_array($query)){

							$content = $row['content'];
							$username = $row['username'];
							$time_created = $row['created_at'];
							$post_id = $row['post_id'];

							if(in_array($username, $post_list) or in_array(getOrgId($username, $conn), $post_list) or $user_type == ADMIN){
								if(getAccountType($username, $conn) == 'teacher'){
									$sql = "select * from teachers where username = '$username';";
									$result = $conn->query($sql);
									while($row = $result->fetch_assoc()) { //teacher post
										$profile_photo = $row['profile_photo'];
										$profile_photo = ($profile_photo == null || empty($profile_photo)) ? DEFAULT_TEACHER_PROFILE_PIC:$profile_photo;
										$post_name = $row["first_name"] .' '. $row["last_name"];
									}
								} else if(getAccountType($username, $conn) == 'organisation'){
									$sql = "select * from organisations where username = '$username';";
									$result = $conn->query($sql);
									while($row = $result->fetch_assoc()) { //org post
										$profile_photo = $row['profile_photo'];
										$profile_photo = ($profile_photo == null || empty($profile_photo)) ? DEFAULT_ORG_PROFILE_PIC:$profile_photo;
										$post_name = $row["name"];
									}
								}

								echo '<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-4">
											<img class="card-img-top rounded-circle" style="height: 100px; width: 100px;" src="'. $profile_photo . '" alt="Profile image" style="width:100%">
										</div>
										<div class="col-6">
											<h4 class="card-title">'. $post_name .'</h4>
											<p class="card-text">'. $content .'</p>
											<button type="button" class="btn btn-primary">Like 👍</button>
										</div>

										<div class="col-2">';

								echo'<p>'. $time_created .'</p>';

								if($_SESSION['username'] == $username or $user_type == ADMIN){
									echo'<button type="button" class="btn btn-outline-danger btn-sm" onclick="deletePost('.$post_id.');">Delete</button>';
								}

								echo'		</div>
										</div>
									</div>
								</div>';
							}
						}
					?>
				</div>
			</div>
		</div>

		<script>

			function handleRefresh() {
				window.location.href = 'feed.php';
			}

      function deletePost(post_id){
				handleRefresh();
			}
		</script>

	</body>
</html>
