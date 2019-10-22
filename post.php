<?php 
include("inc/header.php");
include("inc/classes/User.php");
include("inc/classes/Post.php");

if(isset($_GET["id"])) {
$id = $_GET["id"];
}
else {
$id = -1;
}

?>
<div id="centerer" style="background-color: transparent;">
<div id="index-main-wrapper">
    <div class='index-main-panel'>
            <?php
                
                $post_info = mysqli_query($con, "SELECT * FROM post WHERE id='$id';");
                
                $str = "";
        
                if(mysqli_num_rows($post_info) > 0) {    
        
                    while($row = mysqli_fetch_array($post_info)) {
                        $user_obj = new User($con, $row["user_handle"]);
                        $id = $row['id'];
                        $text = $row['text'];
                        $time = $row['time'];
        
                        $user_profile_pic = $user_obj->get_profile_pic();
                        $user_name = $user_obj->get_name();
                        $poster_handle = $row['user_handle'];
                        $poster_name = $user_obj->get_name_from_handle($poster_handle);
                        $poster_prof_pic = $user_obj->get_profile_pic_from_handle($poster_handle);
        
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
        
                        if(isset($_POST["delete_post" . $id])) {
                            $user_obj->delete_post($id);
                            header("Location: index.php");
                            exit();
                        }
        
                        $time_of_post = $user_obj->calculate_time($row['time']);
                        $num_comments = mysqli_num_rows(mysqli_query($con, "SELECT id FROM comment WHERE post_id='$id'"));
                        $num_likes = mysqli_num_rows(mysqli_query($con, "SELECT id FROM agree WHERE post_id='$id'"));
        
                        echo
                        "<div class='post' style='background:#F2F3F4;border-radius:10px;'>
                            <a href='$poster_handle' style='float:left;padding-right:1%;'><img class='index-profile-pic' src='$poster_prof_pic'/></a>
                            <div class='index-content'>
                                <div class='index-user-name'><a href='$poster_handle'>$poster_name</a></div>
                                <div class='index-time' style='font-weight:bold;'>$time_of_post</div><br>
                                <div class='index-text'>$text</div>
                            </div>";
        
                            if($user_handle == $user_obj->get_poster($id)) {
                                echo "<form method='POST' >
                                    <input type='submit' style='float:right;' class='extreme' name='delete_post$id' value='Delete' >
                                </form>";
                            }
        
                            echo "<span class='likes-stuff'>
                                <iframe src='like.php?post_id=$id' class='like-iframe' scrolling='no' allowtransparency='true' style='background-color: transparent;'></iframe>
                            </class>
        
                            <span class='num-comments' style='text-align:left;cursor:pointer;color:blue;' onclick='javascript:toggle$id()' style='color:blue;'> Comments </span>  ($num_comments)
                            <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameBorder='no' style='resize:vertical;overflow:auto;'></iframe>
        
                            </div>";
        
                    }
        
                }
        
                echo $str;
            ?>
    </div>
</div>
</div>

            </body>

            </html>