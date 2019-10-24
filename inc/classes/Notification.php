<?php

class Notification {
    private $con;
    private $user;

    public function __construct($con, $handle) {
        $this->con = $con;
        $this->user = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$handle';"));
    }

    public function get_unread_number() {
        $user_handle = $this->user["handle"];
        $query = mysqli_query($this->con, "SELECT * FROM notification WHERE viewed='n' AND receiver_handle='$user_handle';");
        return mysqli_num_rows($query);
    }

    public function insert_notification($post_id, $receiver_handle, $type) {
        $user_handle = $this->user["handle"];
        $user_obj = new User($this->con, $user_handle);
        $user_name = $user_obj->get_name();

        $time = date("Y-m-d H:i:s");

        switch($type) {
            case "comment":
                $message = $user_name . " commented on your vibe.";
                break;
            case "like":
                $message = $user_name . " dug your vibe.";
                break;
            case "other_post_comment":
                $message = $user_name . " commented on a vibe.";
                break;
            case "friend_accept":
                $message = $user_name . " accepted your friend request.";
                break;
            case "friend_received":
                $message = $user_name . " sent you a friend request.";
                break;
        } 

        if($type == "friend_accept") {
            $link = "profile.php?profile_handle=" . $user_handle;
        }
        else if($type == "friend_received") {
            $link = "pending_friends.php";
        }
        else {
            $link = "post.php?id=" . $post_id;
        }

        $insert = mysqli_query($this->con, "INSERT INTO notification VALUES('', '$receiver_handle', '$user_handle', '$message', '$link', '$time', 'n');");

    }

    public function getNotifications($data, $limit) {
        $page = $data["page"];
        $user_handle = $this->user["handle"];
        $str = "";

        if($page == 1) {
            $start = 0;
        }
        else {
            $start = ($page - 1) * $limit;
        }

        $set_viewer = mysqli_query($this->con, "UPDATE notification SET viewed='y' WHERE receiver_handle='$user_handle';");

        $query = mysqli_query($this->con, "SELECT * FROM notification WHERE receiver_handle='$user_handle' ORDER BY id DESC;");

        if(mysqli_num_rows($query) == 0) {
            echo "No notifications";
            return;
        }

        $num_iterations = 0;
        $count = 1;

        while($row = mysqli_fetch_array($query)) {
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

            $sender_handle = $row["sender_handle"];
            $query2 = mysqli_query($this->con, "SELECT * FROM user WHERE handle='$sender_handle';");
            $user_data = mysqli_fetch_array($query2);

            $style = /*$row["opened"] == 'n' ? "background-color: #cccccc" : */"";
            $link = $row["link"];
            $prof_pic = $user_data["profile_pic"];
            $time = $this->calculate_time($row["time"]);
            $info = $row["info"];

            $str .= "<a href='$link' style='text-decoration:none;'>
                    <div class='user_found_notifications'>
                    <div class='notifications-profile-pic'>
                        <img src='$prof_pic' style='height:50px;width:50px;border-radius:50%;margin-bottom:4%;margin-left:3%;margin-top:2%;'>

                        <div id='grey' style='margin:0;display:inline-block;margin-top:2%;'>  
                            <p id='grey' style='margin:0;color:blue;'>$info</p>
                            <span class='time-smaller' id='grey' style='color:grey;'>$time</span>
                        </div>
                        </div>
                        </div>
                    </a><hr style='border-top: 1px solid black'>";
        }
        if($count > $limit) {
            $str .= "<input type='hidden' class='nextPageDrowdownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropdownData' value='false'>";
        }
        else {
            $str .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align:center;'>No more notifications.</p>";
        }

        return $str;
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
