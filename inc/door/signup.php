<!-- For sign-up -->
<?php 

$first_name = "";
$last_name = "";
$handle = "";
$email = "";
$password = "";
$profile_pic = "";
$date = "";
$errors = array();
$successes = array();

//sign-up
if(isset($_POST["sign-up-submit"])) {    

    try {
        validate_sign_up_variables($con);
        if(empty($errors)) {
            //encrypting the password    
            $password = md5($password);
            //giving user default profile pic
            $profile_pic = "./assets/img/default_profile_pic.png";
            $date = date('Y-m-d H:i:s');
            $earliest_date = $date;
            $email = !empty($email) ? "'$email'" : "NULL";
            $insert_query = mysqli_query($con, "INSERT INTO user VALUES('', '$handle', '$first_name', '$last_name', $email, '$password', '$date', '$profile_pic', 'n', '$earliest_date', '0')");
            $welcome_message = mysqli_query($con, "INSERT INTO message VALUES('', 'bot', '$handle', '$date', 'Hello! Welcome to the site! :)', 'n', '0', '0');");
            
            array_push($successes, "Sign-up successful");
            //clearing sign-up variables
            $_POST["firstname"] = "";
            $_POST["lastname"] = "";
            $_POST["handle"] = "";
            $_POST["email"] = "";
            $_POST["password"] = "";
            header("Location: index.php");
            exit();
        }
        //error occurred while signing up
        else {

        }
    }
    catch(Exception $e) {
        echo $e;
    }

}

/**
 * Sets the sign-up variables
 */
function validate_sign_up_variables($con) {
    global $errors;
    $first_name = strip_tags($_POST["firstname"]);
    $GLOBALS["first_name"] = str_replace(" ", "", $first_name);
    if(!valid_length($GLOBALS["first_name"], 2, 30)) {
        unset($_SESSION["firstname"]);
        array_push($errors, "First name must be between 2 and 30 characters.");
    }
    $_SESSION["firstname"] = $GLOBALS["first_name"];

    $last_name = strip_tags($_POST["lastname"]);
    $GLOBALS["last_name"] = str_replace(" ", "", $last_name);
    if(!valid_length($GLOBALS["last_name"], 2, 30)) {
        unset($_SESSION["lastname"]);
        array_push($errors, "Last name must be between 2 and 30 characters.");
    }
    $_SESSION["lastname"] = $GLOBALS["last_name"];

    //handle verification
    if(preg_match("/^[A-Za-z0-9_]+$/", $_POST["handle"])) {
        $GLOBALS["handle"] = strip_tags($_POST["handle"]);
        $handle = strip_tags($_POST["handle"]);
        if(mysqli_num_rows(mysqli_query($con, "SELECT handle FROM user WHERE handle='$handle'")) > 0) {
            unset($_SESSION["handle"]);
            array_push($errors, "handle already exists.");
        }
        if(!valid_length($GLOBALS["handle"], 3, 30)) {
            unset($_SESSION["handle"]);
            array_push($errors, "handle must be between 3 and 30 characters.");
        }
    }
    else {
        unset($_SESSION["handle"]);
        array_push($errors, "handle may only contain alphanumeric characters and underscores (_).");
    }
    $_SESSION["handle"] = $GLOBALS["handle"];

    //email verification
    if(strcmp($_POST["email"], "") != 0) {
        if(filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $GLOBALS["email"] = strip_tags($_POST["email"]);
            $email = strip_tags($_POST["email"]);
            if(mysqli_num_rows(mysqli_query($con, "SELECT email FROM user WHERE email='$email'")) > 0) {
                unset($_SESSION["email"]);
                array_push($errors, "Email already exists.");
            }
            if(!valid_length($GLOBALS["email"], 7, 50)) {
                unset($_SESSION["email"]);
                array_push($errors, "Email must be between 7 and 50 characters.");
            }
        }
        else {
            unset($_SESSION["email"]);
            array_push($errors, "Invalid email entered.");
        }
        $_SESSION["email"] = $GLOBALS["email"];
    }

    //password verification
    if(strcmp($_POST["password"], $_POST["password2"]) == 0) {
        $GLOBALS["password"] = $_POST["password"];
        if(!valid_length($GLOBALS["password"], 8, 50)) {
            array_push($errors, "Password must be between 8 and 50 characters.");
        }
    }
    else {
        array_push($errors, "Passwords do not match.");
    }

}

function clear_sign_up_variables() {

}

function valid_length($var, $min_length, $max_length) {
    if(strlen($var) < $min_length || strlen($var) > $max_length) {
       return false;
    }
    return true;
}


?>