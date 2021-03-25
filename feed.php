<!DOCTYPE html> 
<html lang="en">
	<head>
		<meta charset="UTF-8" />
    	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<Title>Home</Title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
        <link type="text/css" rel="stylesheet" href="css/feed.css">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    
	<body>
        
        <?php
            require "database.php";
            require "error.php";
            require "navbar.php";
        ?>
        
        <?php
            generateNavBar(HOME);
        ?>

        <div class="container">
			<div class="row">
				<div class="col-3">
					<div class="card" style="margin-top:20px;">
						<img class="side-bar-profile-pic" src="images/Face.jpg" alt="Profile Pic">
						<div class="card-body text-center">
							<h4 class="card-title">
							    <?php
							    $username = $_SESSION['username'];
							    
                                $sql = "select * from teachers where username = '$username';";
                                $result = $conn->query($sql);
                                while($row = $result->fetch_assoc()) {
                                    echo "" . $row["first_name"] . " " . $row["last_name"] . " ";
                                }
                                ?> 
							</h4>
							    
							<p class="card-text">Some example text some example text. John Doe is an architect and engineer</p>
							<button type="button" class="btn btn-primary">
								Notifications <span class="badge bg-secondary">4</span>
							</button>
						</div>
					</div>
                </div>
                
                <div class="col-9">
					<div class="card" style="margin-top:20px;">
						<div class="form-group">
							<textarea class="form-control" id="exampleFormControlTextarea1" rows="3">
								
							</textarea>
						</div>
						<div class="card-body text-center">
							<button type="button" class="btn btn-primary">Post</button>
						</div>
                    </div>

                    <?php
                        
                            $query = mysqli_query($conn, "select * from posts");
                            while($row = mysqli_fetch_array($query)){
                                $content = $row['content'];
                                $username = $row['username'];
                            
                                $sql = "select * from teachers where username = '$username';";
                                $result = $conn->query($sql);
                                while($row = $result->fetch_assoc()) {
                    
                            echo '<div class="card" style="margin-top:5%; margin-bottom:5%;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <img class="card-img-top" src="images/Face.jpg" alt="Profile image" style="width:100%">
                                    </div>
                                    <div class="col-8">
                                        <h4 class="card-title">'. $row["first_name"] .' '. $row["last_name"] .'</h4>
                                        <p class="card-text">'. $content .'</p>
                                        <button type="button" class="btn btn-primary">Like 👍</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                                }
                            }
                        
                    ?>
				</div>
			</div>
		</div>
		
		
	</body>
</html>