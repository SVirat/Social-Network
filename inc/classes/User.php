<?php
include_once 'Friend.php';

class User {
    private $con;
    private $user;
    //redundant, but stored because it's a unique key frequently queries
    private $handle; 

    public function __construct($con, $handle) {
        $this->con = $con;
        $this->user = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$handle';"));
        $this->handle = $this->user["handle"];
    }

    public function get_name() {
        return $this->user["first_name"] . " " . $this->user["last_name"];
    }

    public function get_handle() {
        return $this->user["handle"];
    }

    public function get_name_from_handle($handle) {
        $user = mysqli_fetch_array(mysqli_query($this->con, "SELECT * FROM user WHERE handle='$handle';"));
        return $user['first_name'] . " " . $user['last_name'];
    }

    public function get_profile_pic_from_handle($handle) {
        $user = mysqli_fetch_array(mysqli_query($this->con, "SELECT * FROM user WHERE handle='$handle';"));
        if (file_exists($user["profile_pic"])) {
            return $user["profile_pic"];
        }
        $this->set_user_profile_pic($handle, "./assets/img/default_profile_pic.png");
        return $user["profile_pic"];
    }

    public function get_email() {
        return $this->user["email"];
    }

    public function get_profile_pic() {
        if (file_exists($this->user["profile_pic"])) {
            return $this->user["profile_pic"];
        }
        $this->set_profile_pic("./assets/img/default_profile_pic.png");
        return $this->user["profile_pic"];
    }

    public function get_poster($post_id) {
        $poster = mysqli_fetch_array(mysqli_query($this->con, "SELECT * FROM post WHERE id='$post_id'"));
        return $poster["user_handle"];
    }

    public function set_first_name($first_name) {
        $first_name_change = mysqli_query($this->con, "UPDATE user SET first_name='$first_name' WHERE handle='$this->handle';");
    }

    public function set_last_name($last_name) {
        $last_name_change = mysqli_query($this->con, "UPDATE user SET last_name='$last_name' WHERE handle='$this->handle';");
    }

    public function set_handle($handle) {
        $num_same_handles = mysqli_num_rows(mysqli_query($this->con, "SELECT * FROM user WHERE handle='$handle';"));
        if($num_same_handles == 0) {
            $handle = strip_tags($handle);
            $handle_change = mysqli_query($this->con, "UPDATE user SET handle='$handle' WHERE handle='$this->handle';");
            $this->handle = $handle;
        }
        else {
            echo "Error: " . $handle . " already exists.";
        }
    }

    public function set_email($email) {
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $num_same_emails = mysqli_num_rows(mysqli_query($this->con, "SELECT * FROM user WHERE email='$email';"));
            if($num_same_emails == 0) {
                $email = strip_tags($email);
                $email_change = mysqli_query($this->con, "UPDATE user SET email='$email' WHERE handle='$this->handle';");
            }
            else {
                echo "Error: " . $email . " already exists.";
            }
        }
        else {
            echo "Error: invalid email.";
        }
    }

    public function verify_password_change($password1, $password2) {
        if(strcmp($password1, $password2) == 0) {
            $this->change_password($password1);
        }
        else {
            echo "Error: passwords do not match.";
        }
    }

    private function set_password($password) {
        $password = md5($password);
        $password_change = mysqli_query($this->con, "UPDATE user SET password='$password' WHERE handle='$this->handle'");
    }

    public function set_profile_pic($prof_pic) {
        $changer = mysqli_query($this->con, "UPDATE user SET profile_pic='$prof_pic' WHERE handle='$this->handle';");
    }

    private function set_user_profile_pic($other_handle, $prof_pic) {
        $changer = mysqli_query($this->con, "UPDATE user SET profile_pic='$prof_pic' WHERE handle='$other_handle';");
    }

    public function delete_post($post_id) {
        $deleter = mysqli_query($this->con, "DELETE FROM post WHERE id='$post_id';");
    }

    /** Friends */

    public function show_friends() {
        $friend_obj = new Friend($this->con, $this->handle);
        return $friend_obj->show_friends('y');
    }

    /**
     * Returns the handles of all of the user's friends
     */
    public function get_friend_list() {
        $friend_obj = new Friend($this->con, $this->handle);
        return $friend_obj->get_friend_list('y');
    }

    /** Posts */

    public function submit_post($text) {
        //sanitizing the text (stripping tags, escaping quotes, handling line breaks)
        $text = nl2br(str_replace('\r\n', '\n', mysqli_real_escape_string($this->con, strip_tags($text))));
        $date = date("Y-m-d H:i:s");

        if(preg_replace('/\s+/', '', $text) != "") {   
            $date = date('Y-m-d H:i:s');
            $post_query = mysqli_query($this->con, "INSERT INTO post VALUES('', '$this->handle', '$text', '$date');");
        }
        //empty post
        else {
            echo "Error: empty post.";
        }
    }

    public function get_all_posts($data, $limit) {
        $page = $data["page"];

        if($page == 1) {
            $start = 0;
        }
        else {
            $start = ($page - 1) * $limit;
        }

        $friend_handles = $this->get_friend_list();

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
                $poster_prof_pic = $this->get_profile_pic_from_handle($poster_handle);

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
                    echo "Helo!!!";
                }

                $time_of_post = $this->calculate_time($row['time']);
                $num_comments = mysqli_num_rows(mysqli_query($this->con, "SELECT id FROM comment WHERE post_id='$id'"));
                $num_likes = mysqli_num_rows(mysqli_query($this->con, "SELECT id FROM agree WHERE post_id='$id'"));

                echo
                "<div class='post'>
                    <a href='$poster_handle' style='padding-right:1%;'><img class='index-profile-pic' src='$poster_prof_pic'/></a>
                    <div class='index-content'>
                        <div class='index-user-name'><a href='$poster_handle'>$poster_name</a></div>
                        <div class='index-time' style='font-weight:bold;'>$time_of_post</div><br>
                        <div class='index-text'>$text</div>
                    </div>

                    <form method='POST' >
                        <input type='submit' style='float:right;' name='delete_post$id'value='Delete' >
                    </form>

                    <span class='likes-stuff'>
                        <iframe src='like.php?post_id=$id' class='like-iframe' scrolling='no' allowtransparency='true' style='background-color: transparent;'></iframe>
                    </class>

                    <span class='num-comments' style='text-align:left;cursor:pointer;color:blue;' onclick='javascript:toggle$id()' style='color:blue;'> Comments </span>  ($num_comments)
                    <div class='post_comment' id='toggleComment$id' style='display:none;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameBorder='no' style='resize:vertical;overflow:auto;'></iframe>

                    </div>

                </div><hr style='border-top: 1px solid black'>";

            }

            if($count > $limit) {
               echo "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                         <input type='hidden' class='noMorePosts' value='false'>";
            }
            else {
               echo "<input type='hidden' class='noMorePosts' value='true'><p class='outrun-font' style='text-align:center;'>All posts seen</p>";
            }

        }

        echo $str;
    }


    /**
     * Gets only user's posts in a limited quantity
     * @param data: the page data of the request, used for infinity scrolling
     * @param limit: the number of posts to dispreturn at a time
     */
    public function get_posts($data, $limit) {

        $page = $data["page"];

        if($page == 1) {
            $start = 0;
        }
        else {
            $start = ($page - 1) * $limit;
        }

        $post_info = mysqli_query($this->con, "SELECT * FROM post WHERE user_handle='$this->handle' ORDER BY id DESC;");
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
                $time_of_post = $this->calculate_time($row['time']);
                $poster_handle = $row['user_handle'];

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
                $num_comments = mysqli_num_rows(mysqli_query($this->con, "SELECT id FROM comment WHERE post_id='$id'"));
                $num_likes = mysqli_num_rows(mysqli_query($this->con, "SELECT id FROM agree WHERE post_id='$id'"));

                $str .= 

                "<div class='post'>
                    <a href='$poster_handle' style='padding-right:1%;'><img class='post-profile-pic' src='$user_profile_pic'/></a>
                    <div class='post-content'>
                        <div class='post-user-name'><a href='$poster_handle'>$user_name</a></div>
                        <div class='post-time'><b>$time_of_post</b></div><br>
                        <div class='post-text'>$text</div> 
                    </div>
                    <div class='more-post-info' style='text-align:center;'>

                    <span class='likes-stuff'>
                        <iframe src='like.php?post_id=$id' class='like-iframe' scrolling='no' allowtransparency='true' style='background-color: transparent;'></iframe>
                    </class>
                        <!-- <span  ><button class='like-button' type='button'>Dig</button></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
                        <!-- <span class='num-likes' style='text-align:left;'> Digs ($num_likes) </span> | -->
                        <span class='num-comments' style='text-align:left;cursor:pointer;color:blue;' onclick='javascript:toggle$id()'> Comments  </span> ($num_comments)
                    </div>
                    <div class='post_comment' id='toggleComment$id' style='display:none;padding-left:5%;'>
                        <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameBorder='no' style='resize:vertical;overflow:auto;'></iframe>

                    </div>
                </div><hr style='border-top: 1px solid black'>";

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

    /** Helper functions */

    public function calculate_time($given_time) {
        $time_now = new DateTime(date('Y-m-d H:i:s'));
        $given_time = new DateTime($given_time);

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
        
        return $time_message;
    }


}
?>