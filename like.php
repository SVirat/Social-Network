<html>
    <head> 
        <link rel="stylesheet" type="text/css" href="./css/style.css">
    </head>

    <body>

        <?php require "./config.php"; 
        include("inc/classes/Friend.php");
        include("inc/classes/User.php"); 
        include("inc/classes/Notification.php"); 
        include("inc/classes/Post.php"); 

        if(isset($_SESSION["handle"])) {
            $handle = $_SESSION["handle"];
            $user = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$handle'"));    
        }
        else {
            header("Location: door.php");
            exit();   
        }

        $user_handle = $user["handle"];

        if(isset($_GET['post_id'])) {
            $post_id = $_GET["post_id"];
        }

        $likes = mysqli_query($con, "SELECT user_handle FROM agree WHERE post_id='$post_id';");
        //list of all people who liked this post
        $likers = array();
        while($row = mysqli_fetch_array($likes)) {
            array_push($likers, $row["user_handle"]);
        }

        //like
        if(isset($_POST["like_button"])) {
            array_push($likers, $user_handle);
            $like_insertion = mysqli_query($con, "INSERT INTO agree VALUES('', '$user_handle', '$post_id');");

            //get whose post was liked
            $get_liked = mysqli_fetch_array(mysqli_query($con, "SELECT user_handle FROM post WHERE id='$post_id';"));
            $get_liked = $get_liked["user_handle"];

            //like notification
            if($get_liked != $user_handle) {
                $notification = new Notification($con, $user_handle);
                $notification->insert_notification($post_id, $get_liked, "like");

            }

        }

        //unlike
        if(isset($_POST["unlike_button"])) {
            $user_handle = $user["handle"];
            if (($key = array_search($user_handle, $likers)) !== false) {
                unset($likers[$key]);
            }
            $like_deletion = mysqli_query($con, "DELETE FROM agree WHERE user_handle='$user_handle' AND post_id='$post_id'");
        }
        
        //check if user liked this post
        $user_liked = mysqli_query($con, "SELECT user_handle FROM agree WHERE post_id='$post_id';");
        $liked = "false";
        while($row = mysqli_fetch_array($user_liked)) {
            if($user_handle == $row["user_handle"]) {
                $liked = "true";
                break;
            }
        }

        if($liked == "true") {
            echo "<form action='like.php?post_id=" . $post_id ."' method='POST'><input type='submit' class='unlike_button' name='unlike_button' value='Undig' style='background: #F2F3F4;
            border-radius: 5%;
            cursor: pointer; 
            border: none;
            margin-top: 4%;
            padding-top: 4%;
            color: var(--blue-color);
            transition: 0.3s;
            margin-left: 1.5%;'/>
                <div class='like-value' style='display:inline;'> (" . count($likers) . ") </div>
            </form>";
        }
        else {
            echo "<form action='like.php?post_id=" . $post_id ."' method='POST'><input type='submit' class='like_button' name='like_button' value='Dig' style=' background: #F2F3F4;
            border-radius: 5%;
            margin-left: 1.5%;
            cursor: pointer;
            margin-top: 4%;
            padding-top: 4%;
            color: var(--blue-color);
            box-shadow: none;
            border: none;'/>
                <div class='like-value' style='display:inline;font-family:\'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;'> (" . count($likers) . ") </div>
            </form>";
        }


        ?>

    </body>


</html>