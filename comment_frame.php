<html>
<head>
    <link rel="stylesheet" type="text/css" href="./css/style.css">
    <link rel="stylesheet" type="text/css" href="./css/index.css">
  
</head>
<body style='background:#cccccc;'>

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

    ?>

    <script>
    function toggle() {
        var commentSection = document.getElementById("comment-section");
        if(commentSection.style.display == "block") {
            commentSection.style.display = "none";
        }
        else {
            commentSection.style.display = "block";
        }
    }
    </script>

    <?php 
        if(isset($_GET['post_id'])) {
            $post_id = $_GET["post_id"];
        }

        $post = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM post WHERE id='$post_id'"));
        $poster = $post["user_handle"];

        if(isset($_POST["postComment" . $post_id])) {
            $comment_body = mysqli_escape_string($con, $_POST["comment_body"]);
            $time = date("Y-m-d H:i:s");
            $insert_comment = mysqli_query($con, "INSERT INTO comment VALUES('', '$handle', '$comment_body', '$post_id', '$time');");

            $user_handle = $user["handle"];

            //get post's user
            $get_poster = mysqli_fetch_array(mysqli_query($con, "SELECT user_handle FROM post WHERE id='$post_id';"));
            $get_poster = $get_poster["user_handle"];

            //get post's commenters
            $get_commenters = mysqli_query($con, "SELECT user_handle FROM comment WHERE post_id='$post_id';");

            //comment notification for poster
            if($get_poster != $user_handle) {
                $notification = new Notification($con, $user_handle);
                $notification->insert_notification($post_id, $get_poster, "comment");
            }

            //comment notification for all commenters
            $notified_commenters = array();
            while($row = mysqli_fetch_array($get_commenters)) {
                $commenter_handle = $row["user_handle"];
                if($commenter_handle != $get_poster && $commenter_handle != $user_handle && !in_array($commenter_handle, $notified_commenters)) {
                    $notification = new Notification($con, $user_handle);
                    $notification->insert_notification($post_id, $commenter_handle, "other_post_comment");
                    array_push($notified_commenters, $commenter_handle);
                }
            }



        }

    ?>

    <form action="comment_frame.php?post_id=<?php echo $post_id; ?>" class="comment_form" name="postComment<?php echo $post_id?>" method="POST">
        <textarea name="comment_body" placeholder="Comment your thoughts..." required></textarea>
        <!-- For whatever reason, iframe's submit button CSS isn't styling button properly, so manually styling it here -->
        <input type="submit" name="postComment<?php echo $post_id; ?>" value="Comment" class="comment-submit" style="vertical-align:top;height:45px;">
    </form>

    <!-- Displaying comments -->
    <?php 
        $get_comments = mysqli_query($con, "SELECT * FROM comment WHERE post_id='$post_id' ORDER BY date;");
        $num_comments = mysqli_num_rows($get_comments);
        $user_handle = $user["handle"];

        //TODO: append the comments, you're currently replacing them
        if($num_comments > 0) {
            while($comment = mysqli_fetch_array($get_comments)) {
                $commenter = $comment["user_handle"];
                $comment_body = $comment["comment"];
                $time = $comment["date"];
                $comment_id = $comment["id"];

                //calculating time
                $time_now = new DateTime(date('Y-m-d H:i:s'));
                $given_time = new DateTime($time);

                $interval = $given_time->diff($time_now);
                $time_message = "";

                if($interval->y >= 1) {
                    if($interval->y == 1) {
                        $time_message = "1 year ago";
                    }
                    else {
                        $time_message = $interval->y . " years ago";
                    }
                }
                else if($interval->m >= 1) {
                    if($interval->m == 1) {
                        $time_message = "1 month ago";
                    }
                    else {
                        $time_message = $interval->m . " months ago";
                    }
                }
                else if($interval->d >= 1) {
                    if($interval->d == 1) {
                        $time_message = "Yesterday";
                    }
                    else {
                        $time_message = $interval->d . " days ago";
                    }
                }
                else if($interval->h >= 1) {
                    if($interval->h == 1) {
                        $time_message = "1 hour ago";
                    }
                    else {
                        $time_message = $interval->h . " hours ago";
                    }
                }
                else if($interval->i >= 1) {
                    if($interval->i == 1) {
                        $time_message = "1 min ago";
                    }
                    else {
                        $time_message = $interval->i . " mins ago";
                    }
                }
                else {
                    $time_message = "Just now";
                }
                

                if(isset($_POST["delete_comment" . $comment_id])) {
                    $delete_comment = mysqli_query($con, "DELETE FROM comment WHERE id='$comment_id'");
                    header("Refresh:0");
                    exit();
                }

                $user_obj = new User($con, $commenter);
                ?>
                <br>
                <div class="comment_section" id="commie">
                    <hr>
                    <a href="<?php echo $commenter?>" target="_parent">
                        <div class="comment-profile-pic">
                        
                        <img src="<?php echo $user_obj->get_profile_pic();?>" title="<?php echo $commenter;?>" class="comment-profile-pic" style="float:left;width: 50px;padding-left:1%;padding-right:1%;height: 50px;border-radius:50%;">
                        </div>
                        <div class="comment-content">
                            <a href="<?php echo $commenter?>" target="_parent" class="comment-user-name" style="font-family:'Candara';text-decoration:none;"><b><?php echo $user_obj->get_name();?></b></a>
                            <div class="comment-time" style="font-family:'Candara';"><b><?php echo $time_message; ?></b>  <?php
                        if($user_handle == $commenter) {
                            echo "<form method='POST' >
                                <input type='submit' style='float:right;' class='extreme' name='delete_comment$comment_id' value='X' >
                            </form>";
                        }
                        ?></div><br>
                        
                            <div class="comment-text" style="font-family:'Candara';"><?php echo $comment_body; ?></div><br>



                        </div>

                        
                       

                    </a>
                </div>

<?php
            }
        }


    ?>

    


</body>

</html>