<!DOCTYPE html>
<html lang="en">

<?php
session_start();

include("connection.php");

$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error) {
    die("Error: Couldn't connect. ". $conn->connect_error);
}

if(!isset($_SESSION["student_id"])) {
    header("location: login.php");
}

if($_SESSION["permission"] != 0) {
    echo "
			<script>
				alert('☒ Incorrect Permissions!');
			</script>
		";
    header("location: index.php");
}

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

//table stuff

$prepareQueryInfo = "SELECT * FROM users_info ORDER BY student_id;";
$queryInfo = $conn->prepare($prepareQueryInfo);
$queryInfo->execute();
$resultInfo = $queryInfo->get_result(); 

$prepareQueryProgram = "SELECT * FROM users_program ORDER BY student_id;";
$queryProgram = $conn->prepare($prepareQueryProgram);
$queryProgram->execute();
$resultProgram = $queryProgram->get_result();

$prepareQueryPerm = "SELECT * FROM users_permissions ORDER BY student_id;";
$queryPerm = $conn->prepare($prepareQueryPerm);
$queryPerm->execute();
$resultPerm = $queryPerm->get_result();

function check_email($email, $conn) {
	$emailQuery = $conn->query("SELECT student_email FROM users_info;");
	$emailList = $emailQuery->fetch_array();
	if(in_array($email, $emailList)) {
		return true;
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
			<h2>User List</h2>
            <form>
            <fieldset>
                <?php 
                echo 
                    "<table>
                        <tr>
                            <th>Student_id</th>
                            <th>First_name</th>
                            <th>Last_name</th>
                            <th>Student_email</th>
                            <th>Program</th>
                            <th>Account_type</th>
                        </tr>";
                while(($info = $resultInfo->fetch_assoc()) 
                    && ($program = $resultProgram->fetch_assoc())
                    && ($account = $resultPerm->fetch_assoc())){
                    echo 
                        "<tr>
                            <td>'". $info['student_id'] ."'</td>" .
                            "<td>'". $info['first_name'] ."'</td>" .
                            "<td>'". $info['last_name'] ."'</td>" .
                            "<td>'". $info['student_email'] ."'</td>" .
                            "<td>'". $program['program'] ."'</td>" .
                            "<td>'". $account['account_type'] ."'</td>" .
                        "</tr>";
                }
                echo "</table>";
                ?>
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
</body>

</html>