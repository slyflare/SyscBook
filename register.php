<!DOCTYPE html>
<html lang="en">

<?php
session_start();

include("connection.php");

$conn = new mysqli($servername, $username, $password, $dbname);

$emailValidate = 0;

if($conn->connect_error) {
	die("Error: Couldn't connect. ". $conn->connect_error);
}

if(isset($_SESSION['student_id'])){
	//profile
	$id = $_SESSION["student_id"];
	$prepareQueryInfoSummary = "SELECT * FROM users_info WHERE student_id = ?;";
	$queryInfoSummary = $conn->prepare($prepareQueryInfoSummary);
	$queryInfoSummary->bind_param("i", $id);
	$queryInfoSummary->execute();
	$resultInfoSummary = $queryInfoSummary->get_result(); 

	$prepareQueryProgramSummary = "SELECT * FROM users_program WHERE student_id = ?;";
	$queryProgramSummary = $conn->prepare($prepareQueryProgramSummary);
	$queryProgramSummary->bind_param("i", $id);
	$queryProgramSummary->execute();
	$resultProgramSummary = $queryProgramSummary->get_result();

	$prepareQueryAvatarSummary = "SELECT * FROM users_avatar WHERE student_id = ?;";
	$queryAvatarSummary = $conn->prepare($prepareQueryAvatarSummary);
	$queryAvatarSummary->bind_param("i", $id);
	$queryAvatarSummary->execute();
	$resultAvatarSummary = $queryAvatarSummary->get_result();

	$infoSummary = $resultInfoSummary->fetch_assoc();
	$programSummary = $resultProgramSummary->fetch_assoc();
	$avatarSummary = $resultAvatarSummary->fetch_assoc();
}

if(isset($_POST["submit"])) {
	if(!check_email($_POST["student_email"], $conn)) {
		$prepareInfo = "INSERT INTO users_info(student_email, first_name, last_name, dob) VALUES (?, ?, ?, ?)";
		$insertInfo = $conn->prepare($prepareInfo);
		$insertInfo->bind_param("sssb", $_POST["student_email"], $_POST["first_name"], $_POST["last_name"], $_POST["DOB"]);
		$insertInfo->execute();

		$id = mysqli_insert_id($conn);
		$_SESSION["student_id"] = $id;

		$prepareProgram = "INSERT INTO users_program(student_id, Program) VALUES (?, ?)";
		$insertProgram = $conn->prepare( $prepareProgram);
		$insertProgram->bind_param("is", $id, $_POST["program"]);
		$insertProgram->execute();

		$prepareAvatar = "INSERT INTO users_avatar(student_id) VALUES (?)";
		$insertAvatar = $conn->prepare($prepareAvatar);
		$insertAvatar->bind_param("i", $id);
		$insertAvatar->execute();

		$prepareAddress = "INSERT INTO users_address(student_id) VALUES (?)";
		$insertAddress = $conn->prepare($prepareAddress);
		$insertAddress->bind_param("i", $id);
		$insertAddress->execute();

		$hashed = password_hash($_POST["confirm_password"], PASSWORD_BCRYPT);
		$preparePassword = "INSERT INTO users_passwords(student_id, password) VALUES (? ,?)";
		$insertPassword = $conn->prepare($preparePassword);
		$insertPassword->bind_param("is", $id, $hashed);
		$insertPassword->execute();

		$preparPerms = "INSERT INTO users_permissions(student_id) VALUES (?)";
		$insertPerms = $conn->prepare($preparPerms);
		$insertPerms->bind_param("i", $id);
		$insertPerms->execute();

		$pepareQueryPerm = "SELECT account_type FROM users_permissions WHERE student_id = ?;";
		$queryPerm = $conn->prepare($pepareQueryPerm);
		$queryPerm->bind_param("s", $resultId);
		$queryPerm->execute();
		$resultPerm = $queryPerm->get_result();

        $_SESSION["premission"] = $resultPerm;

		$conn -> close();
		header("location: profile.php");
		exit();
	} else {
		$emailValidate = 1;
	}
}

function check_email($email, $conn) {
	$emailQuery = $conn->query("SELECT student_email FROM users_info;");
	while($emailList = $emailQuery->fetch_assoc()) {
		if($emailList['student_email'] == $email) {
			return true;
		}
	}
	return false;
}

$conn -> close();
?>

<head>
<meta charset="utf-8">
<title>Register on SYSCX</title>
<link rel="stylesheet" href="assets/css/reset.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
<header>
	<h1>SYSCX</h1>
	<p>Social media for SYSC students in Carleton University</p>
</header>
<div class="container">
	<nav>
		<?php 
			if(isset($_SESSION['student_id'])) {
				echo'
					<a href="index.php">Home</a>
					<a href="profile.php">Profile</a>
				';
				if($_SESSION['permission'] == 0) {
					echo '<a href="user_list.php">User List</a>';
				}
				echo'
					<a href="logout.php">Log out</a>
				';
			} else {
				echo'
					<a href="index.php">Home</a>
					<a href="register.php">Register</a>
					<a href="login.php">Log in</a>
				';
			}
		?>
	</nav>

	<main>
		<section>
			<h2>Register a new profile</h2>
			<form method="post" action="">
			<fieldset>
				<legend> 
					<span>Personal information</span>
				</legend>
				<table>
					<tr>
						<td>
						<label>First Name:</label>
						<input name="first_name" type="text">
						</td>
						<td>
						<label>Last Name:</label>
						<input name="last_name" type="text">
						</td>
						<td>
						<label>DOB:</label>
						<input name="DOB" type="date">
						</td>
					</tr>
				</table>
				<legend>
					<span>Profile information</span>
				</legend>
				<table>
					<tr>
						<td>
						<p style="color:red"><?php if($emailValidate) echo "☒ Account exists with the same email! Submit form with a different email!";?></p>
						<label>Email address:</label>
						<input name="student_email" type="text" onkeyup="validate_email()" required/>
						</td>
					</tr>
					<tr>
						<td>
						<label>Program:</label>
						<select name="program" >
							<option selected value="Choose Program">Choose Program</option>
							<option value="Computer System Engineering">Computer System Engineering</option>
							<option value="Software Engineering">Software Engineering</option>
							<option value="Communications Engineering">Communicatsions Engineering</option>
							<option value="Biomedical and Electrical">Biomedical and Electrical</option>
							<option value="Electrical Engineering">Electrical Engineering</option>
							<option value="Special">Special</option>
						</select>
						</td>
					</tr>
					<tr>
						<td>
						<p id="wrong_pass_alert"></p>
						<label>Password:</label>
						<input name="password" id="password" type="password" placeholder="Enter Password" required>
						</td>
					</tr>
					<tr>
						<td>
						<label>Confirm Password:</label>
						<input name="confirm_password" id="confirm_password" type="password" placeholder="Confirm Password" required onkeyup="validate_password()">
						</td>
					</tr>
					<tr>
						<td>
						<input id="submit" name ="submit" type="submit" value="Register">
						<input type="reset">
						</td>
					</tr>
				</table>
				<legend>
					<span>Already Registered?</span>
				</legend>
				<table>
					<tr>
						<td>
							<input type="button" value="Login" onclick="location.href='login.php'"/>
						</td>
					</tr>
				</table>
			</fieldset>
			</form>
		</section>
	</main>

	<div class="profileSummary">
        <?php
            if(isset($_SESSION['student_id'])) {
                echo'
                    <h2 id="profileSummaryName">'.$infoSummary["first_name"]." ".$infoSummary["last_name"].'</h2>
                    <img id="profileSummaryAvatar" src="images/img_avatar'. $avatarSummary['avatar'].'.png" alt="">
                    <h2>Email:</h2>
                    <a id="profileSummaryEmail" href="'.$infoSummary["student_email"].'">'.$infoSummary["student_email"].'</a>
                    <h2>Program:</h2>
                    <h2 id="profileSummaryProgram">'.$programSummary['program'].'</h2>
                ';
            }
        ?>
    </div>
</div>
<script>
	function validate_password() {
		let pass = document.getElementById('password').value;
		let confirm_pass = document.getElementById('confirm_password').value;
		if (pass != confirm_pass) {
			document.getElementById('wrong_pass_alert').style.color = 'red';
			document.getElementById('wrong_pass_alert').innerHTML
				= '☒ Passwords do not match';
			document.getElementById('submit').disabled = true;
			document.getElementById('submit').style.opacity = (0.4);
		} else {
			document.getElementById('wrong_pass_alert').style.color = 'green';
			document.getElementById('wrong_pass_alert').innerHTML =
				'🗹 Passwords Matched';
			document.getElementById('submit').disabled = false;
			document.getElementById('submit').style.opacity = (1);
		}
	}
</script>
</body>

</html>