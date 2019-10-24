<?php include("inc/header.php"); 
echo "<script>changeActive('settings')</script>";

$user_handle = $user["handle"];
$errors = array();

if(isset($_POST["first-name-change"])) {
    $new_name = $_POST["firstname-change"];
    $changer = mysqli_query($con, "UPDATE user SET first_name='$new_name' WHERE handle='$user_handle';");
    header("Location: settings.php");
    exit();
}

if(isset($_POST["last-name-change"])) {
    $new_name = $_POST["lastname-change"];
    $changer = mysqli_query($con, "UPDATE user SET last_name='$new_name' WHERE handle='$user_handle';");
    header("Location: settings.php");
    exit();
}

if(isset($_POST["handle-change"])) {
    $handle1 = $_POST["handle-change-1"];
    $handle2 = $_POST["handle-change-2"];

    if(strcmp($handle1, $handle2) != 0) {
        array_push($errors, "Handles do not match.");
    }
    else {
            $handle1 = strip_tags($handle1);
            if(mysqli_num_rows(mysqli_query($con, "SELECT handle FROM user WHERE handle='$handle1'")) > 0) {
                array_push($errors, "Handle already exists.");
            }
            else if(strlen($handle1) < 3 || strlen($handle1) > 30) {
                array_push($errors, "Handle must be between 3 and 30 characters.");
            }
            else {
                $email_change = mysqli_query($con, "UPDATE user SET handle='$handle1' WHERE handle='$user_handle';");
                header("Location: settings.php");
                exit();
            }
        }
    }
}

if(isset($_POST["email-change"])) {
    $email1 = $_POST["email-change-1"];
    $email2 = $_POST["email-change-2"];

    if(strcmp($email1, $email2) != 0) {
        array_push($errors, "Emails do not match.");
    }
    else {
        if(filter_var($email1, FILTER_VALIDATE_EMAIL)) {
            $email1 = strip_tags($email1);
            if(mysqli_num_rows(mysqli_query($con, "SELECT email FROM user WHERE email='$email1'")) > 0) {
                array_push($errors, "Email already exists.");
            }
            else if(strlen($email1) < 7 || strlen($email1) > 50) {
                array_push($errors, "Email must be between 7 and 50 characters.");
            }
            else {
                $email_change = mysqli_query($con, "UPDATE user SET email='$email1' WHERE handle='$user_handle';");
                header("Location: settings.php");
                exit();
            }
        }
        else {
            array_push($errors, "Invalid email entered.");
        }
    }
}

if(isset($_POST["password-change"])) {
    
    if($user_handle == "visiter" || $user_handle == "visitor") {
        echo '<script language="javascript">';
        echo 'alert("Password change is disabled for the visitor profile.")';
        echo '</script>';
    }
    else {
        $password = $_POST["password-change-1"];
        $password1 = $_POST["password-change-2"];

        if(strcmp($password, $password1) == 0) {
            if(strlen($password) < 8 || strlen($password) > 50) {
                array_push($errors, "Password must be between 8 and 50 characters.");
            }
            else {
                $password = md5($password);
                $password_change = mysqli_query($con, "UPDATE user SET password='$password' WHERE handle='$user_handle';");
                header("Location: door.php");
                exit();
            }
        }
        else {
            array_push($errors, "Passwords do not match.");
        }
    }
}

if(isset($_POST["deactivate"])) {
    if($user_handle == "visiter" || $user_handle == "visitor") {
        echo '<script language="javascript">';
        echo 'alert("Deactivation is disabled for the visitor profile.")';
        echo '</script>';
    }
    else {
        $deactivate = mysqli_query($con, "UPDATE user SET deactivated='y' WHERE handle='$handle'");
        header("Location: door.php");
    }
}

if(isset($_POST["delete"])) { 
    if($user_handle == "visiter" || $user_handle == "visitor") {
        echo '<script language="javascript">';
        echo 'alert("Deletion is disabled for the visitor profile.")';
        echo '</script>';
    }
    else {
        $delete = mysqli_query($con, "DELETE FROM user WHERE handle='$user_handle';");
        header("Location: door.php");
    }
}

?>

<div id="photo-changer">
    <div id="changer-announcer"><b>Change Profile Picture</b></div><br><br>
    <div class="inline-wrapper">
    <img id="user-prof-pic" src=<?php echo $user["profile_pic"];?>>
        <form action='upload.php' method='POST' enctype='multipart/form-data' style="display:inline-block;">
            <input type='file' name='fileToUpload' id='fileToUpload'>
            <br>
            <input type='submit' value='Upload Image' class="changer-button" onclick="crop();"name='submit'>
        </form>
        
    </div>
</div>

<div id="changer">
    <div id="name-change-announcer"><b>Change Name</b></div><br>
    <form method="POST" class="first-name-changer">
        <b>First Name:</b> <input type="text" name="firstname-change" class="field-long" placeholder="New First Name">
        <input type='submit' value='Change' class="changer-button" name='first-name-change'>
    </form>
    <form method="POST" class="last-name-changer">
        <b>Last Name:</b> <input type="text" name="lastname-change" class="field-long" placeholder="New Last Name">
        <input type='submit' value='Change' class="changer-button" name='last-name-change'>
    </form>
    <br>
    
    <div id="handle-change-announcer"><b>Change Handle</b></div><br>
    <form method="POST" class="handle-changer">
        <b>New Handle:</b> <input type="text" name="handle-change-1" class="field-long" placeholder="New Handle"><br>
        <b>New Handle:</b> <input type="text" name="handle-change-2" class="field-long" placeholder="Retype New Handle"><br>
        <input type='submit' value='Change' class="changer-button" name='handle-change'>
    </form>
    <?php if(in_array("Handles do not match.", $errors)) echo "Handles do not match.<br>"?>
    <?php if(in_array("Handle already exists.", $errors)) echo "Handle already exists.<br>"?>
    <?php if(in_array("Handle must be between 3 and 30 characters.", $errors)) echo "Handle must be between 3 and 30 characters.<br>"?>
    
    <br>
    <div id="email-change-announcer"><b>Change Email</b></div><br>
    <form method="POST" class="email-changer">
        <b>New Email:</b> <input type="text" name="email-change-1" class="field-long" placeholder="New Email"><br>
        <b>New Email:</b> <input type="text" name="email-change-2" class="field-long" placeholder="Retype New Email"><br>
        <input type='submit' value='Change' class="changer-button" name='email-change'>
    </form>
    <?php if(in_array("Emails do not match.", $errors)) echo "Emails do not match.<br>"?>
    <?php if(in_array("Email already exists.", $errors)) echo "Email already exists.<br>"?>
    <?php if(in_array("Email must be between 7 and 50 characters.", $errors)) echo "Email must be between 7 and 50 characters.<br>"?>
    <?php if(in_array("Invalid email entered.", $errors)) echo "Invalid email entered.<br>"?>

    <br>
    <div id="password-change-announcer"><b>Change Password</b><br><i>You will be logged out</i></div><br>
    <form method="POST" class="password-changer">
        <b>New Password:</b> <input type="password" name="password-change-1" class="field-long" placeholder="New Password"><br>
        <b>New Password:</b> <input type="password" name="password-change-2" class="field-long" placeholder="Retype New Password"><br>
        <input type='submit' value='Change' class="changer-button" name='password-change'>
    </form>
    <?php if(in_array("Password must be between 8 and 50 characters.", $errors)) echo "Password must be between 8 and 50 characters.<br>"?>
    <?php if(in_array("Passwords do not match.", $errors)) echo "Passwords do not match.<br>"?>

</div>

<div id="deactivater">
    <form method='POST'>
        <input type='submit' value='Deactivate Account' onclick="return confirm('Are you sure?');" class="delete-button" name='deactivate'>
    </form>

    <form method='POST'>
        <input type='submit' value='Delete Account' onclick="return confirm('Are you sure?');" class="delete-button" name='delete'>
    </form>
</div>

</body>

</html>
