<?php 
class Post {
    private $con;
    private $user;
    //redundant, but stored because it's a unique key frequently queries
    private $handle;
    
    private $post;

    public function __construct($con, $handle) {
        $this->con = $con;
        $this->user = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$handle';"));
        $this->handle = $this->user["handle"];
    }

    public function get_single_post($post_id) {

        //all of user's posts UNION all of friends' posts
        $post_info = mysqli_query($this->con, "SELECT * FROM post WHERE id='$post_id';");
        $str = "";

        if(mysqli_num_rows($post_info) > 0) {    


            while($row = mysqli_fetch_array($post_info)) {

                $id = $row['id'];
                $text = $row['text'];
                $time = $row['time'];

                $user_obj = new User($this->con, $row["user_handle"]);

                $user_profile_pic = $user_obj->get_profile_pic();;
                $user_name = $user_obj->get_name();
                $poster_handle = $row['user_handle'];
                $poster_name = $user_obj->get_name_from_handle($poster_handle);

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


                $time_of_post = $user_obj->calculate_time($row['time']);
                

                $str .= 
                "<div class='post' onclick='javascript:toggle$id()'>
                    <img class='index-profile-pic' src='$user_profile_pic'/>
                    <div class='index-content'>
                        <div class='index-user-name'><a href='$poster_handle'>$poster_name</a></div>
                        <div class='index-time'>$time_of_post</div><br>
                        <div class='index-text'>$text</div>
                    </div>
                    <div class='post_comment' id='toggleComment$id' style='display:none;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe'></iframe>

                    </div>
                </div>
                <hr>";

            }

        }

        echo $str;
       
    }

    public function get_all_posts($data, $limit) {
        $page = $data["page"];

        if($page == 1) {
            $start = 0;
        }
        else {
            $start = ($page - 1) * $limit;
        }

        $friend_handles = $this->get_friend_list('y');

        //all of user's posts UNION all of friends' posts
        $post_info = mysqli_query($this->con, "(SELECT * FROM post WHERE user_handle='$this->handle') UNION
        (SELECT * FROM post WHERE user_handle IN ('".implode("','",$friend_handles)."'))
        ORDER BY time DESC;");
        $str = "";

        if(mysqli_num_rows($post_info) > 0) {    

            $num_iterations = 0;
            $count = 1;

            while($row = mysqli_fetch_array($post_info)) {

                if($num_iterations < $start) {
                    $num_iterations++;
                    continue;
                }

                if($count > $limit) {
                    break;
                }
                else {
                    $count++;
                }

                $id = $row['id'];
                $text = $row['text'];
                $time = $row['time'];

                $user_profile_pic = $this->user['profile_pic'];
                $user_name = $this->get_name();
                $poster_handle = $row['user_handle'];
                $poster_name = $this->get_name_from_handle($poster_handle);

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


                $time_of_post = $this->calculate_time($row['time']);
                

                $str .= 
                "<div class='post' onclick='javascript:toggle$id()'>
                    <img class='index-profile-pic' src='$user_profile_pic'/>
                    <div class='index-content'>
                        <div class='index-user-name'><a href='$poster_handle'>$poster_name</a></div>
                        <div class='index-time'>$time_of_post</div><br>
                        <div class='index-text'>$text</div>
                    </div>
                    <div class='post_comment' id='toggleComment$id' style='display:none;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe'></iframe>

                    </div>
                </div>
                <hr>";

            }

            if($count > $limit) {
                $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                         <input type='hidden' class='noMorePosts' value='false'>";
            }
            else {
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p class='outrun-font' style='text-align:center;'>All posts seen</p>";
            }

        }

        echo $str;
    }



}

?>