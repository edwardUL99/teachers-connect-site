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
         ?>

        <?php
            generateNavBar(HOME);
        ?>
        
        <div class="container">
            <div class="row shadow card padding-1pcent mt-5">
                <div class="col padding-1pcent" id="add_skills">
                    <h4>Add Skills</h4>
                    <form id="add_skills_form">
                        <div class="form-group">
                            <label>Skills</label>
                            <input type="text" name="skills" id="skills" oninput="onSkillsInput();" class="form-control" required>
                            <div class="form-text">
                                Enter tags to add in a comma-separated (,) list
                            </div>
                        </div>
                        <div class="row text-end mt-2">
                            <div class="col">
                                <button type="button" onClick="handleNewSkills();" id="add_skills_button" class="btn btn-primary">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col padding-1pcent" id="remove_skill">
                    <h4>Remove Tags</h4>
                    <form id="remove_skill_form">
                        <div class="form-group">
                            <label>Choose skills to tags</label>
                            <select class="form-select" multiple id="skills_choice" onchange="onSkillChosen();" name="skills_choice">
                                <?php getSkillsOptions(); ?>
                            </select>
                            <div class="form-text">
                                Select the skill to tag from the list
                            </div>
                        </div>
                        <div class="row text-end mt-2">
                            <div class="col">
                                <button type="button" onClick="handleDeleteSkill();" id="remove_skill_button" class="btn btn-primary">Remove</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
