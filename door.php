<?php require "./config.php"; ?>

<?php include("./inc/door/signup.php"); ?>
<?php include("./inc/door/login.php"); ?>

<?php session_destroy(); ?>

<!doctype html>
<html>
    <head>
    <script src="js/door.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Audiowide" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet">
        <title>PalWeb</title>
        <link rel="stylesheet" type="text/css" href="./css/door.css">
    </head>

<body>
    <header id="navbar">
        <a href="#about" id="about-navbar"  onclick="show(this.id);changeActive(this.id);">About</a>
        <a href="#login" id="login-navbar" class="active" onclick="show(this.id);changeActive(this.id);">Login</a>
        <a href="#sign-up" id="signup-navbar" onclick="show(this.id);changeActive(this.id);">Sign Up</a>
    </header>
    
    <!-- About Section -->
    <div id="about" style="display:none">
        <br>
        <h1 class="glow classy-font">
            <center>Welcome to PalWeb!</center>
        </h1>
        <br>
        <div class="about-info">
            PalWeb is a personal project inspired by sites like Facebook and Twitter, made specifically for programmers and engineers 
            to get feedback or help on their personal projects, share their opinions on the industry, or simply expand their network.
            Features include activities such as adding and deleting posts and comments, liking other posts, adding and removing friends, 
            calculating mutual friends, receiving notifications, searching for other users, and so on.<br><br>
            It was built in the back-end with PHP and JavaScript, and in the front-end with HTML and CSS. Ajax calls are used to 
            dynamically feed the user data from the server. The database was also designed with ACID properties in mind, 
            with an emphasis on minimizing redundancy and maximizing consistency.<br><br>
            To see the full code base and view the DBMS schema, visit the <a href="https://github.com/SVirat/social-network" target="_blank">github repo</a>. 
            <br><br>
            To learn more about me and my profile, visit my personal portfolio site: <a href="https://svirat.github.io" target="_blank">svirat.github.io</a>.
        </div>
    </div>

    <!-- Login Section -->
    <div id="login" class="forms">
        <?php if(in_array("Sign-up successful", $successes)) { 
            echo "<br><span class='success'>Sign up successful!</span><br>";
            $successes = array();
        }?>
        <h2 class="glow classy-font">
            Login
        </h2>
        <form method="POST" class="form-style-1">
            <label><b>Handle:</b></label><input type="text" name="handle" class="field-long" placeholder="Handle" required/>
            <label><b>Password:</b></label><input type="password" name="password" class="field-long" placeholder="Password" required/><br><br>
            <center><input type="submit" name="login-submit" value="Submit"/></center>
            <?php if(in_array("Login error. Try again.", $errors)) echo "<br><center>Login error. Try again.</center>   <br>"?>
        </form>

        <br><br>
        If you simply want to test the site, use the following credentials:<br>
        <table>
            <tr>
                <td><b>Handle: </b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td>visiter</td>
            </tr>
            <tr>
                <td><b>Password: &nbsp;&nbsp;&nbsp;&nbsp;</b></td>
                <td>password</td>
            </tr>
        </table>

    </div>

    <!-- Sign-Up Section -->
    <div id="sign-up" class="forms" style="display:none">
        <h2 class="glow classy-font">
            Sign up!
        </h2>
        <form method="POST" class="form-style-1">
            <label><b>First Name:</b> <span class="required">*</span></label><input type="text" name="firstname" class="field-long" placeholder="First Name" value="<?php 
                if(isset($_SESSION["firstname"])) {
                    echo $_SESSION["firstname"];
                }
            ?>" required/>
            <?php if(in_array("First name must be between 2 and 30 characters.", $errors)) echo "First name must be between 2 and 30 characters.<br>"?>
            <label><b>Last Name:</b> <span class="required">*</span></label><input type="text" name="lastname" class="field-long" placeholder="Last Name" value="<?php 
                if(isset($_SESSION["lastname"])) {
                    echo $_SESSION["lastname"];
                }
            ?>" required/>
            <?php if(in_array("Last name must be between 2 and 30 characters.", $errors)) echo "Last name must be between 2 and 30 characters.<br>"?>
            <label><b>Handle:</b> <span class="required">*</span></label><input type="text" name="handle" class="field-long" placeholder="Handle" value="<?php 
                if(isset($_SESSION["handle"])) {
                    echo $_SESSION["handle"];
                }
            ?>" required/>
            <?php if(in_array("handle already exists.", $errors)) echo "handle already exists.<br>"?>
            <?php if(in_array("handle must be between 3 and 30 characters.", $errors)) echo "handle must be between 3 and 30 characters.<br>"?>
            <?php if(in_array("handle may only contain alphanumeric characters and underscores (_).", $errors)) echo "handle may only contain alphanumeric characters and underscores (_).<br>"?>
            <label><b>Email:</b></label>
            <input type="email" name="email" class="field-long" placeholder="user.name@example.com" value="<?php 
                if(isset($_SESSION["email"])) {
                    echo $_SESSION["email"];
                }
            ?>" />
            <?php if(in_array("Email already exists.", $errors)) echo "Email already exists.<br>"?>
            <?php if(in_array("Email must be between 7 and 50 characters.", $errors)) echo "Email must be between 7 and 50 characters.<br>"?>
            <?php if(in_array("Invalid email entered.", $errors)) echo "Invalid email entered.<br>"?>
            <label><b>Password:</b> <span class="required">*</span></label><input type="password" name="password" class="field-long" placeholder="Password" required/>
            <?php if(in_array("Password must be between 8 and 50 characters.", $errors)) echo "Password must be between 8 and 50 characters.<br>"?>
            <label><b>Re-type Password:</b> <span class="required">*</span></label><input type="password" name="password2" class="field-long" placeholder="Password" required/>
            <?php if(in_array("Passwords do not match.", $errors)) echo "Passwords do not match.<br>"?>
            <br><br>
            <center><input type="submit" name="sign-up-submit" value="Submit"/></center>
        </form>
    </div>
        
</body>

</html>


