<!DOCTYPE html>
<html lang="en">

<!--
SYSC 4504 Assignment 1
Vimal Gunasegaran, 101155249
-->

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

$id = $_SESSION['student_id'];

$prepareQueryInfo = "SELECT * FROM users_info WHERE student_id = ?;";
$queryInfo = $conn->prepare($prepareQueryInfo);
$queryInfo->bind_param("i", $id);
$queryInfo->execute();
$resultInfo = $queryInfo->get_result(); 

$prepareQueryProgram = "SELECT * FROM users_program WHERE student_id = ?;";
$queryProgram = $conn->prepare($prepareQueryProgram);
$queryProgram->bind_param("i", $id);
$queryProgram->execute();
$resultProgram = $queryProgram->get_result();

$prepareQueryAvatar = "SELECT * FROM users_avatar WHERE student_id = ?;";
$queryAvatar = $conn->prepare($prepareQueryAvatar);
$queryAvatar->bind_param("i", $id);
$queryAvatar->execute();
$resultAvatar = $queryAvatar->get_result();

$info = $resultInfo->fetch_assoc();
$program = $resultProgram->fetch_assoc();
$avatar = $resultAvatar->fetch_assoc();

$prepareQuery = "SELECT post_id, new_post FROM users_posts ORDER BY post_date DESC LIMIT 10;";
$select = $conn->prepare($prepareQuery);
$select->execute();
$result = $select->get_result();

if(isset($_POST["submit"])) {
    $prepareInsert = "INSERT INTO users_posts(student_id, new_post) VALUES (?, ?)";
    $insertProgram = $conn->prepare($prepareInsert);
    $insertProgram->bind_param("is", $id, $_POST['new_post']);
    $insertProgram->execute();

    header("Refresh:0");
}

$conn -> close();
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        //Profile Summary loading
        document.getElementById("profileSummaryName").textContent = "<?php echo $info["first_name"]." ".$info["last_name"] ?>";
        document.getElementById("profileSummaryAvatar").src = "images/img_avatar"+ <?php echo$avatar['avatar'];?> + ".png";
        document.getElementById("profileSummaryEmail").setAttribute('href', "<?php echo $info["student_email"];?>");
        document.getElementById("profileSummaryEmail").textContent = "<?php echo $info["student_email"];?>";
        document.getElementById("profileSummaryProgram").textContent = "<?php echo $program['program'];?>";
    });
</script>

<head>
<meta charset="utf-8">
<title>Home on SYSCX</title>
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
            <h2>New Post</h2>
            <form method="post" action="">
            <fieldset>
                <textarea name="new_post"></textarea>
                <table>
                    <tr>
                        <td>
                        <input type="submit" name="submit" value="Post">
                        <input type="reset">
                        </td>
                    </tr>
                </table>
            </fieldset>
            </form>
        </section>

        <?php
            while($row = $result->fetch_assoc()){
                echo "
                    <div class='messageBox'>\n
                        <h2>&#9660;Post ".$row['post_id']."</h2>\n
                        <p>".$row['new_post']."</p>\n   
                    </div>\n
                    ";
            }
        ?>
    </main>

    <div class="profileSummary">
        <?php
            if(isset($_SESSION['student_id'])) {
                echo'
                    <h2 id="profileSummaryName">'.$info["first_name"]." ".$info["last_name"].'</h2>
                    <img id="profileSummaryAvatar" src="images/img_avatar'. $avatar['avatar'].'.png" alt="">
                    <h2>Email:</h2>
                    <a id="profileSummaryEmail" href="'.$info["student_email"].'">'.$info["student_email"].'</a>
                    <h2>Program:</h2>
                    <h2 id="profileSummaryProgram">'.$program['program'].'</h2>
                ';
            }
        ?>
    </div>
</div>
</body>

</html>