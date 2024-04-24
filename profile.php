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

$prepareQueryAddress = "SELECT * FROM users_address WHERE student_id = ?;";
$queryAddress = $conn->prepare($prepareQueryAddress);
$queryAddress->bind_param("i", $id);
$queryAddress->execute();
$resultAddress = $queryAddress->get_result();

$info = $resultInfo->fetch_assoc();
$program = $resultProgram->fetch_assoc();
$avatar = $resultAvatar->fetch_assoc();
$address = $resultAddress->fetch_assoc();

if(isset($_POST["submit"])) {
    $prepareUpdateInfo = "UPDATE users_info SET student_email = ?, first_name = ?, last_name = ?, dob = ? WHERE student_id = ?;";
    $updateInfo = $conn->prepare($prepareUpdateInfo);
    $updateInfo->bind_param("sssbi", $_POST['student_email'], $_POST['first_name'], $_POST['last_name'], $_POST['DOB'], $id);
    $updateInfo->execute();

    $prarepareUpdateProgram = "UPDATE users_program SET Program = ? WHERE student_id = ?;";
    $updateProgram = $conn->prepare($prarepareUpdateProgram);
    $updateProgram->bind_param("si", $_POST['program'], $id);
    $updateProgram->execute();

    $prepareUpdateAvatar = "UPDATE users_avatar SET avatar = ? WHERE student_id = ?;";
    $updateAvatar = $conn->prepare($prepareUpdateAvatar);
    $updateAvatar->bind_param("ii", $_POST["avatar"], $id);
    $updateAvatar->execute();

    $prarepareUpdateProgram = "UPDATE users_address SET street_number = ?, street_name = ?, city = ?, province = ?, postal_code = ? WHERE student_id = ?;";
    $updateProgram = $conn->prepare($prarepareUpdateProgram);
    $updateProgram->bind_param("issssi", $_POST["street_number"], $_POST["street_name"], $_POST["city"], $_POST["province"], $_POST["postal_code"], $id );
    $updateProgram->execute();

    header("Refresh:0");
}

$conn -> close();
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        //Form loading
        document.getElementById("first_name").value = "<?php if($info != null) {echo $info["first_name"];} else {echo "";}?>";
        document.getElementById('last_name').value = "<?php if($info != null) {echo $info["last_name"];} else {echo "";}?>";
        document.getElementById('DOB').value = "<?php if($info != null) {echo $info["dob"];} else {echo "";}?>";
        document.getElementById('student_email').value = "<?php if($info != null) {echo $info["student_email"];} else {echo "";}?>";

        document.getElementById('program').value = "<?php if($program != null) {echo $program['program'];} else {echo "Choose Program";}?>";

        if(<?php if($avatar['avatar'] != 0) {echo "true";} else {echo "false";}?>){
            document.getElementById('avatar'+"<?php echo $avatar['avatar'];?>").checked = true;
        }

        document.getElementById('street_number').value = "<?php if($address != null) {echo $address["street_number"];} else {echo "";}?>";
        document.getElementById('street_name').value = "<?php if($address != null) {echo $address["street_name"];} else {echo "";}?>";
        document.getElementById('city').value = "<?php if($address != null) {echo $address["city"];} else {echo "";}?>";
        document.getElementById('province').value = "<?php if($address != null) {echo $address["province"];} else {echo "";}?>";
        document.getElementById('postal_code').value = "<?php if($address != null) {echo $address["postal_code"];} else {echo "";}?>";

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
<title>Update SYSCX profile</title>
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
            <h2>Update Profile information</h2>
            <form method="post" action="">
            <fieldset>
                <legend> 
                    <span>Personal information</span>
                </legend>
                <table>
                    <tr>
                        <td>
                        <label>First Name:</label>
                        <input id="first_name" name="first_name" type="text" value="">
                        </td>
                        <td>
                        <label>Last Name:</label>
                        <input id="last_name" name="last_name" type="text">
                        </td>
                        <td>
                        <label>DOB:</label>
                        <input id="DOB" name="DOB" type="date">
                        </td>
                    </tr>
                </table>
                <legend> 
                    <span>Address</span>
                </legend>
                <table>
                    <tr>
                        <td>
                        <label>Street Number:</label>
                        <input id="street_number" name="street_number" type="text">
                        </td>
                        <td colspan="2">
                        <label>Street Name:</label>
                        <input id="street_name" name="street_name" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <label>City:</label>
                        <input id="city" name="city" type="text">
                        </td>
                        <td>
                        <label>Province:</label>
                        <input id="province" name="province" type="text">
                        </td>
                        <td>
                        <label>Postal Code:</label>
                        <input id="postal_code" name="postal_code" type="text">
                        </td>
                    </tr>
                </table>
                <legend>
                    <span>Profile information</span>
                </legend>
                <table>
                    <tr>
                        <td>
                        <label>Email address:</label>
                        <input id="student_email" name="student_email" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <label>Program:</label>
                        <select id="program" name="program" >
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
                        <label>Choose your Avatar</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <input id="avatar1" name="avatar" type="radio" value="1"><img src="images/img_avatar1.png" alt="" class="radio">
                        <input id="avatar2" name="avatar" type="radio" value="2"><img src="images/img_avatar2.png" alt="" class="radio">
                        <input id="avatar3" name="avatar" type="radio" value="3"><img src="images/img_avatar3.png" alt="" class="radio">
                        <input id="avatar4" name="avatar" type="radio" value="4"><img src="images/img_avatar4.png" alt="" class="radio">
                        <input id="avatar5" name="avatar" type="radio" value="5"><img src="images/img_avatar5.png" alt="" class="radio">
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <input type="submit" name="submit">
                        <input type="reset">
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