<!DOCTYPE html>
<html lang="en">

<?php
session_start();

include("connection.php");

$conn = new mysqli($servername, $username, $password, $dbname);

$userValidate = 0;

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

if(isset($_POST["login"])) {
	if(check_email($_POST["student_email"], $conn)) {
        $prepareQueryId = "SELECT student_id FROM users_info WHERE student_email = ?;";
        $queryId = $conn->prepare($prepareQueryId);
        $queryId->bind_param("s", $_POST['student_email']);
        $queryId->execute();
        $resultId = $queryId->get_result();
		$id = $resultId->fetch_assoc();

        $pepareQueryPass = "SELECT password FROM users_passwords WHERE student_id = ?;";
        $queryPass = $conn->prepare($pepareQueryPass);
        $queryPass->bind_param("i", $id['student_id']);
        $queryPass->execute();
        $resultPass = $queryPass->get_result();
		$pass = $resultPass->fetch_assoc();
        
        if(password_verify($_POST['password'],$pass['password'])) {
            $pepareQueryPerm = "SELECT account_type FROM users_permissions WHERE student_id = ?;";
            $queryPerm = $conn->prepare($pepareQueryPerm);
            $queryPerm->bind_param("i", $id['student_id']);
            $queryPerm->execute();
            $resultPerm = $queryPerm->get_result();
			$perm = $resultPerm->fetch_assoc();

            $_SESSION["student_id"] = $id['student_id'];
            $_SESSION["permission"] = $perm['account_type'];
            header("location: index.php");
            exit();
        } else {
			$userValidate = 1;
        }
	} else {
		$userValidate = 1;
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
			if(isset($_SESSION['student_id']) && isset($_SESSION['permission']) ) {
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
			<h2>Login</h2>
			<form method="post" action="">
			<fieldset>
                <legend>
                </legend>
				<table>
					<tr>
						<p style="color:red"><?php if($userValidate) echo "☒ Incorrect Email or Password!";?></p>
						<td>
						<label>Email Address:</label>
						<input name="student_email" type="text">
						</td>
					</tr>
                    <tr>
                        <td>
						<label>Password:</label>
						<input name="password" type="password">
						</td>
                    </tr>
					<tr>
						<td>
						<input id="login" name ="login" type="submit" value="Login">
						</td>
					</tr>
				</table>
                <legend>
					<span>Not Registered?</span>
				</legend>
				<table>
					<tr>
						<td>
							<input type="button" value="Register" onclick="location.href='register.php'"/>
						</td>
					</tr>
				</table>
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
</body>

</html>