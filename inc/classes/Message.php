<?php
class Message {
    private $con;
    private $user;

    public function __construct($con, $handle) {
        $this->con = $con;
        $this->user = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$handle';"));
    }

    public function get_most_recent_user() {
        $user_handle = $this->user["handle"];
        $most_recent = mysqli_query($this->con, "SELECT sender_handle, receiver_handle FROM message WHERE sender_handle='$user_handle' OR 
                                                receiver_handle='$user_handle'ORDER BY time DESC LIMIT 1;");
        if(mysqli_num_rows($most_recent) == 0) {
            return "false";
        }
        $row = mysqli_fetch_array($most_recent);
        $sender_handle = $row["sender_handle"];
        $receiver_handle = $row["receiver_handle"];

        if($sender_handle == $user_handle) {
            return $receiver_handle;
        }
        return $sender_handle;
    }

    public function send_message($receiver_handle, $body) {
        if($body != "") {
            $user_exists = mysqli_query($this->con, "SELECT handle FROM user WHERE handle='$receiver_handle';");
            if(mysqli_num_rows($user_exists) > 0) {
                $time = date("Y-m-d H:i:s");
                $sender_handle = $this->user["handle"];
                $sender = mysqli_query($this->con, "INSERT INTO message VALUES('', '$sender_handle', '$receiver_handle', '$time', '$body', 'n', '0', 'n');");
            }
            else {
                echo "No such user.";
            }
        }
        else {
            echo "Message body cannot be empty.";
        }
    }

    public function get_messages($receiver_handle) {
        $user_handle = $this->user["handle"];

        $query = mysqli_query($this->con, "SELECT * FROM message WHERE viewed='y' AND 
                            sender_handle='$user_handle' AND receiver_handle='$receiver_handle';");

        $message_query = mysqli_query($this->con, "SELECT * FROM message WHERE (sender_handle='$user_handle' AND receiver_handle='$receiver_handle')
                                                    OR (sender_handle='$receiver_handle' AND receiver_handle='$user_handle');");

        $data = "";
        while($row = mysqli_fetch_array($message_query)) {
            $sender_handle = $row["sender_handle"];
            $receiver_handle = $row["receiver_handle"];
            $time = $row["time"];
            $body = $row["body"];
            $viewed = $row["viewed"];
            $opened_time = $row["opened_time"];
            $opened = $row["opened"];

            $div_top = ($receiver_handle == $user_handle) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";
            $data .= $div_top . $body . "</div><br><br><br>";

        }

        return $data;
    }

    public function get_latest_message($other_handle) {
        $user_handle = $this->user["handle"];
        $details = array();
        $row = mysqli_fetch_array(mysqli_query($this->con, "SELECT * FROM message WHERE (sender_handle='$user_handle' AND receiver_handle='$other_handle') 
                                            OR (receiver_handle='$user_handle' AND sender_handle='$other_handle') ORDER BY id DESC LIMIT 1;"));
        $sent_by = ($row["sender_handle"] == $user_handle) ? "<b>You:</b>" : "";
        $time = $this->calculate_time($row["time"]);
        array_push($details, $sent_by);
        array_push($details, $row["body"]);
        array_push($details, $time);

        return $details;

    }

    public function getConvos() {
        $user_handle = $this->user["handle"];
        $convos = array();
        $str = "";

        $query = mysqli_query($this->con, "SELECT * FROM message WHERE sender_handle='$user_handle' OR receiver_handle='$user_handle' ORDER BY id DESC;");

        while($row = mysqli_fetch_array($query)) {
            $user_to_push = ($row["sender_handle"] == $user_handle) ? $row["receiver_handle"] : $row["sender_handle"];

            if(!in_array($user_to_push, $convos)) {
                array_push($convos, $user_to_push);
            }
        }

        foreach($convos as $username) {
            $user_found_obj = new User($this->con, $username);
            $other_prof_pic = $user_found_obj->get_profile_pic();
            $other_name = $user_found_obj->get_name();

            $latest_message_details = $this->get_latest_message($username);

            $dots = strlen($latest_message_details[1]) >= 24 ? "..." : "";
            $split = str_split($latest_message_details[1], 24);
            $split = $split[0] . $dots;

            $str .= "<a href='messages.php?u=$username' style='text-decoration:none;'>
                        <div class='user_found_messages'>
                            <img src='$other_prof_pic' style='height:50px;width:50px;border-radius:50%;float:left;margin-right:5px;margin-bottom:4%;'>
                            <div style='display:inline-block;float:left;width:80%;'><span style='float:left;'><b>$other_name</b></span><span class='time-smaller' id='grey' style='color:grey;float:right;'>$latest_message_details[2]</span><br>
                            <p id='grey' style='margin:0;color:grey;float:left;'>$latest_message_details[0] $split</p></div>
                        </div>
                    </a><br><br><br><hr style='border-top: 1px solid black'>";

        }

        return $str;

    }

    public function getConvosDropdown($data, $limit) {
        $page = $data["page"];
        $user_handle = $this->user["handle"];
        $str = "";
        $convos = array();

        if($page == 1) {
            $start = 0;
        }
        else {
            $start = ($page - 1) * $limit;
        }

        $set_viewer = mysqli_query($this->con, "UPDATE message SET viewed='y' WHERE receiver_handle='$user_handle';");

        $query = mysqli_query($this->con, "SELECT * FROM message WHERE sender_handle='$user_handle' OR receiver_handle='$user_handle' ORDER BY id DESC;");

        while($row = mysqli_fetch_array($query)) {
            $user_to_push = ($row["sender_handle"] == $user_handle) ? $row["receiver_handle"] : $row["sender_handle"];

            if(!in_array($user_to_push, $convos)) {
                array_push($convos, $user_to_push);
            }
        }

        $num_iterations = 0;
        $count = 1;

        foreach($convos as $username) {
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

            $is_unread_query = mysqli_query($this->con, "SELECT opened FROM message WHERE receiver_handle='$user_handle' AND sender_handle='$username' ORDER BY id DESC;");
            $row = mysqli_fetch_array($is_unread_query);
            $style = /*$row["opened"] == 'n' ? "background-color: #cccccc" : */"";


            $user_found_obj = new User($this->con, $username);
            $other_prof_pic = $user_found_obj->get_profile_pic();
            $other_name = $user_found_obj->get_name();

            $latest_message_details = $this->get_latest_message($username);

            $dots = strlen($latest_message_details[1]) >= 24 ? "..." : "";
            $split = str_split($latest_message_details[1], 24);
            $split = $split[0] . $dots;

            $str .= "<a href='messages.php?u=$username' style='text-decoration:none;'>
                        <div class='user_found_messages' style='$style';>
                            <img src='$other_prof_pic' style='height:50px;width:50px;border-radius:50%;margin-top:5px;margin-left:5px;margin-right:5px;margin-bottom:4%;'>
                            <div style='display:inline-block;margin-top:5px;'><b>$other_name</b>      <span class='time-smaller' id='grey' style='color:grey;'>$latest_message_details[2]</span>
                            <p id='grey' style='margin:0;color:grey;'>$latest_message_details[0] $split</p></div>
                        ";
            if($num_iterations < count($convos) - 1) {
                $str .= "<hr style='border-top: 1px solid black'></div>
                </a>";
            }
        }
        if($count > $limit) {
            $str .= "<input type='hidden' class='nextPageDrowdownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropdownData' value='false'>";
        }
        else {
            $str .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align:center;'><a href='messages.php?u=n'>Send new</a></p>";
        }

        return $str;
    }

    public function get_unread_number() {
        $user_handle = $this->user["handle"];
        $query = mysqli_query($this->con, "SELECT * FROM message WHERE viewed='n' AND receiver_handle='$user_handle';");
        return mysqli_num_rows($query);
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