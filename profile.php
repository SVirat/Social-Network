<?php include("inc/header.php"); 
include ("inc/classes/User.php");
echo "<script>changeActive('profile')</script>";

if(isset($_GET["profile_handle"])) {
    $profile_handle = $_GET["profile_handle"];
    $profile_details = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$profile_handle';"));
    if($profile_details["deactivated"] == 'y') {
        header("Location: closed_profile.php");
        exit();
    }
    $profile_name = $profile_details['first_name'] . " " . $profile_details['last_name'];
    $profile_signup_time = $profile_details['sign_up_time'];
    $profile_prof_pic = $profile_details['profile_pic'];
    $profile_last_login = $profile_details['latest_login_time'];

    //get number of friends
    $num_friends = mysqli_num_rows(mysqli_query($con, "SELECT id FROM friend WHERE (sender_handle='$profile_handle' OR receiver_handle='$profile_handle') AND accepted='y';"));
    $num_comments = mysqli_num_rows(mysqli_query($con, "SELECT id FROM comment WHERE user_handle='$profile_handle';"));
    $num_posts = mysqli_num_rows(mysqli_query($con, "SELECT id FROM post WHERE user_handle='$profile_handle';"));
    $num_likes = mysqli_num_rows(mysqli_query($con, "SELECT id FROM agree WHERE user_handle='$profile_handle'"));

    $user_obj = new User($con, $profile_handle);
    $profile_last_login = $user_obj->calculate_time($profile_last_login);
}

if(isset($_POST["remove_friend"])) {
    $user_handle = $user['handle'];
    $remover_obj = new Friend($con, $user_handle);
    $remover_obj->delete_friend($profile_handle);
    header("Location: $profile_handle");
    exit();
}

if(isset($_POST["add_friend"])) {
    $user_handle = $user['handle'];
    $remover_obj = new Friend($con, $user_handle);
    $remover_obj->send_friend_request($profile_handle);
    header("Location: $profile_handle");
    exit();
}

if(isset($_POST["accept_friend_req"])) {
    $user_handle = $user['handle'];
    $remover_obj = new Friend($con, $user_handle);
    $remover_obj->accept_friend_request($profile_handle);
    header("Location: $profile_handle");
    exit();
}

if(isset($_POST["send_message"])) {
    header("Location: messages.php?u=$profile_handle");
    exit();
}

?>
<div id='profile-page' style="background-color: transparent;">

<div id='profile-left'>
    <img src="<?php echo $profile_prof_pic;?>" >
    <div id='profile-name'><?php echo $profile_name;?></div>

    <form action=<?php echo $profile_handle; ?> method="POST">
    <?php
        $friend_obj = new Friend($con, $user['handle']);
        if($user['handle'] != $profile_handle) {
            echo "<br><input type='submit' class='safe' name='send_message' action='messages.php?u=$profile_handle' value='Message'><br>";
            if($friend_obj->is_friend($profile_handle)) {
                echo "<br><input type='submit' name='remove_friend' class='extreme' value='Remove Friend' >";
            }
            else if($friend_obj->sent_friend_request($profile_handle)) {
                echo "<br><input type='submit' name='remove_friend' class='extreme' value='Withdraw Friend Request' >";
            }
            else if($friend_obj->received_friend_request($profile_handle)) {
                echo "<br><input type='submit' class='safe' style='margin-bottom: 4%;' name='accept_friend_req' value='Accept Friend Request' >";
                echo "<br><input type='submit' class='extreme' name='remove_friend' value='Delete Friend Request' >";
            }
            else {
                echo "<br><input type='submit' class='safe' name='add_friend' value='Add Pal' >";
            }
        }
    ?>
    </form>

    <hr style='border-top: 1px solid blue'>
    <div class='profile-info'>
        <span class='profile-info-inner'>Pals:</span> <?php echo $num_friends;?><br>
        <span class='profile-info-inner'>Comments:</span> <?php echo $num_comments;?><br>
        <span class='profile-info-inner'>Vibes:</span> <?php echo $num_posts;?><br>
        <span class='profile-info-inner'>Digs:</span> <?php echo $num_likes;?><br>    
        <span class='profile-info-inner'>Last login:</span> <?php echo $profile_last_login;?><br>          
        <span class='profile-info-inner'>Joined:</span> <?php echo date("m/Y", strtotime($profile_signup_time));?><br>   
    </div>

</div>

<div id='profile-main'>
    <div id='activity-announce'><?php echo $profile_name?>'s Activity: </div>

<?php
        $post_info = mysqli_query($con, "SELECT * FROM post WHERE user_handle='$profile_handle' ORDER BY id DESC;");
        $str = "";

        if(mysqli_num_rows($post_info) > 0) {    

            $num_iterations = 0;

            while($row = mysqli_fetch_array($post_info)) {
                $num_iterations++;

                $id = $row['id'];
                $text = $row['text'];
                $time = $row['time'];

                $user_profile_pic = $user_obj->get_profile_pic();
                $user_name = $user_obj->get_name();
                $time_of_post = $user_obj->calculate_time($row['time']);
                
                $user_obj = new User($con, $profile_handle);

                ?>
                <script>
                    function toggle<?php echo $id; ?>() {
                        var commentSection = document.getElementById("toggleComment<?php echo $id; ?>");
                        if(commentSection.style.display == "block") {
                            commentSection.style.display = "none";
                        }
                        else {
                            commentSection.style.display = "block";
                        }
                    }
                </script>

                <?php
                $num_comments = mysqli_num_rows(mysqli_query($con, "SELECT id FROM comment WHERE post_id='$id'"));
                $num_likes = mysqli_num_rows(mysqli_query($con, "SELECT id FROM agree WHERE post_id='$id'"));

                if(isset($_POST["delete_post" . $id])) {
                    $user_obj->delete_post($id);
                    header("Location: $user_handle");
                    exit();
                }

                echo 

                "<div class='post'>
                    <a href='$profile_handle' style='padding-right:1%;'><img class='post-profile-pic' src='$user_profile_pic'/></a>
                    <div class='post-content'>
                        <div class='post-user-name'><a href='$profile_handle'>$user_name</a></div>
                        <div class='post-time'><b>$time_of_post</b></div><br>
                        <div class='post-text'>$text</div> 
                    </div>";

                    if($user["handle"] == $user_obj->get_poster($id)) {
                        echo "<form method='POST' >
                            <input type='submit' style='float:right;' class='extreme' name='delete_post$id' value='Delete' >
                        </form>";
                    }
                    echo "<div class='more-post-info' style='text-align:center;'>

                    <span class='likes-stuff'>
                        <iframe src='like.php?post_id=$id' class='like-iframe' scrolling='no' allowtransparency='true' style='background-color: transparent;'></iframe>
                    </class>
                        <!-- <span  ><button class='like-button' type='button'>Dig</button></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
                        <!-- <span class='num-likes' style='text-align:left;'> Digs ($num_likes) </span> | -->
                        <span class='num-comments' style='text-align:left;cursor:pointer;color:blue;' onclick='javascript:toggle$id()'> Comments  </span> ($num_comments)
                    </div>
                    <div class='post_comment' id='toggleComment$id' style='display:none;padding-left:5%;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameBorder='no' style='resize:vertical;overflow:auto;'></iframe>

                    </div>";
               
                if($num_iterations < (mysqli_num_rows($post_info))) {
                    echo "</div><hr style='border-top: 1px solid black'>";
                }


            }

        }
        ?>


    <div class="more-posts"></div>
</div>

</div>