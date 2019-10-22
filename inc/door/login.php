<?php 

if(isset($_POST["login-submit"])) {
    try {
        validate_login($con);
    }
    catch(Exception $e) {
        echo $e;
    }
}

function validate_login($con) {
    global $errors;
    $handle = $_POST["handle"];
    $_SESSION["handle"] = $handle;
    $password = md5($_POST["password"]);

    //attempt to login
    $login_query = mysqli_query($con, "SELECT * FROM user WHERE handle='$handle' AND password='$password'");
    if(mysqli_num_rows($login_query) == 1) {
        $user_info = mysqli_fetch_array($login_query);
        $handle = $user_info["handle"];
        $_SESSION["handle"] = $handle;

        //reactivate account if deactivated
        $deactivated_account = $user_info["deactivated"];
        if(strcmp($deactivated_account, "y") == 0) {
            $reactivate_query = mysqli_query($con, "UPDATE user SET deactivated='n' WHERE handle='$handle'");
        }

        //set latest login time
        $date = date('Y-m-d H:i:s');
        $login_time_query = mysqli_query($con, "UPDATE user SET latest_login_time='$date' WHERE handle='$handle'");

        //redirect to homepage
        header("Location: index.php");
        exit();
    }
    else {
        array_push($errors, "Login error. Try again.");
    }
}

?>