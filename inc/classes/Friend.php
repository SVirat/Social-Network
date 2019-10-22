<?php 

class Friend {
    private $con;
    private $user_handle;

    public function __construct($con, $user_handle) {
        $this->con = $con;
        $this->user_handle = $user_handle;
    }

    public function is_friend($other_handle) { 
        return in_array($other_handle, $this->get_friend_list('y'));
    }

    public function sent_friend_request($other_handle) {
        $sent_friend = mysqli_num_rows(mysqli_query($this->con, "SELECT * FROM friend WHERE receiver_handle='$other_handle' AND 
                                                                sender_handle='$this->user_handle' AND accepted='n';"));
       return $sent_friend != 0;
    }

    public function received_friend_request($other_handle) {
        $got_friend = mysqli_num_rows(mysqli_query($this->con, "SELECT * FROM friend WHERE sender_handle='$other_handle' AND 
                                                                receiver_handle='$this->user_handle' AND accepted='n';"));
        return $got_friend != 0;
    }

    public function send_friend_request($other_handle) {
        $time = date("Y-m-d H:i:s");
        $sender = mysqli_query($this->con, "INSERT INTO friend VALUES('', '$this->user_handle', '$other_handle', 'n', '$time');");
    }

    public function accept_friend_request($other_handle) {
        $time = date("Y-m-d H:i:s");
        $accepter = mysqli_query($this->con, "UPDATE friend SET accepted='y', time='$time' WHERE sender_handle='$other_handle' AND receiver_handle='$this->user_handle';");
    }

    public function delete_friend($other_handle) {
        $deleter = mysqli_query($this->con, "DELETE FROM friend WHERE (sender_handle='$this->user_handle' AND receiver_handle='$other_handle')
        OR (sender_handle='$other_handle' AND receiver_handle='$this->user_handle');");
    }

    public function get_friend_list($accepted) {

        $friend_list = array();
        $raw_friend_list = $this->get_raw_friend_list($accepted);
        
        //creating a copy-array
        $friend_list_copy = array();
        for($i = 0; $i < count($raw_friend_list); $i++) {
            $row = $raw_friend_list[$i];
            array_push($friend_list_copy, $row);
        }

        //going through user's friends list
        for($x = 0; $x < count($friend_list_copy); $x++ ) {
            $row = $friend_list_copy[$x];

            //finding friend's handle
            if(strcmp($row["sender_handle"], $this->user_handle) == 0) {
                $friend_handle = $row["receiver_handle"];
            }
            else {
                $friend_handle = $row["sender_handle"];
            }

            array_push($friend_list, $friend_handle);

        }

        return $friend_list;
    }

    public function show_specific_user($other_handle) {
        $str .= 
        "<div class='friend'>
            <a href='$other_handle' style='padding-right:1%;'><img class='friend-profile-pic' src='$friend_profile_pic'/></a>
            <div class='friend-content'>
                <div class='friend-name'><a href='$other_handle'>$friend_name</a></div>
                <div class='friends-since' ><span style='font-weight:bold;'>Pending since:</span> $time_added</div><br>
                <div class='mutual-friends' onclick='toggleMutualShower("."\"".$other_handle."\"".");'><span style='font-weight:bold;cursor:pointer;color:blue' >Mutual friends:</span> $num_mutual_friends</div>
                ";

            if($accepted == 'n') {    
                $str .= "<form action='$friend_handle'>
                    <input type='submit' style='float:right;' class='view' value='View' />
                </form>";
            }

            $str.="</div><div class='mutual-friends-shower' id='$friend_handle'style='display:none;border-radius:10px;'>";
    }

    public function show_friends($accepted) {

        $str = "";
        $raw_friend_list = $this->get_raw_friend_list($accepted);
        $actual_friend_list = $this->get_raw_friend_list('y');

        //creating two copy-arrays because we need two nested loops to iterate to find friends
        $user_friend_array_iteration = array();

        $friend_friend_array_iteration_pending = array();
        $friend_friend_array_iteration = array();

        for($i = 0; $i < count($raw_friend_list); $i++) {
            $row = $raw_friend_list[$i];
            array_push($user_friend_array_iteration, $row);
            array_push($friend_friend_array_iteration, $row);
        }
        for($i = 0; $i < count($actual_friend_list); $i++) {
            $row = $actual_friend_list[$i];
            array_push($friend_friend_array_iteration_pending, $row);
        }

        //going through user's friends list
        for($x = 0; $x < count($user_friend_array_iteration); $x++ ) {
            $row = $user_friend_array_iteration[$x];

            //finding friend's handle
            if(strcmp($row["sender_handle"], $this->user_handle) == 0) {
                $friend_handle = $row["receiver_handle"];
            }
            else {
                $friend_handle = $row["sender_handle"];
            }

            //since friend_handle is unique key, this will only return one row and there is on need for another while loop
            $friend_info = mysqli_fetch_array(mysqli_query($this->con, "SELECT * FROM user WHERE handle='$friend_handle';"));

            $friend_profile_pic = $friend_info["profile_pic"];
            $friend_name = $friend_info["first_name"] . " " . $friend_info["last_name"];
            $time_added = $this->calculate_time($row["time"]);

            //finding mutual friends
            if($accepted == 'y') {
                $mutual_friends = $this->find_mutual_friends($friend_friend_array_iteration, $friend_handle);
            }
            else {
                $mutual_friends = $this->find_mutual_friends($friend_friend_array_iteration_pending, $friend_handle);
            }
            $num_mutual_friends = count($mutual_friends);
            $mutual_friends_string = implode(', ', $mutual_friends);

            $added_or_pending_since = $accepted == 'y' ? "Added" : "Pending since";

            $str .= 
            "<div class='friend'>
                <a href='$friend_handle' style='padding-right:1%;'><img class='friend-profile-pic' src='$friend_profile_pic'/></a>
                <div class='friend-content'>
                    <div class='friend-name'><a href='$friend_handle'>$friend_name</a></div>
                    <div class='friends-since' ><span style='font-weight:bold;'>$added_or_pending_since:</span> $time_added</div><br>
                    <div class='mutual-friends' onclick='toggleMutualShower("."\"".$friend_handle."\"".");'><span style='font-weight:bold;cursor:pointer;color:blue' >Mutual friends:</span> $num_mutual_friends</div>
                    ";

                if($accepted == 'n') {    
                    $str .= "<form action='$friend_handle'>
                        <input type='submit' style='float:right;' class='view' value='View' />
                    </form>";
                }

                $str.="</div><div class='mutual-friends-shower' id='$friend_handle'style='display:none;border-radius:10px;'>";

            
            //going in reverse order of array, which means chronological order of friend addition
            for($i = $num_mutual_friends - 1; $i >= 0; $i--) {
                $user_obj = new User($this->con, $mutual_friends[$i]);
                $mut_friend_handle = $mutual_friends[$i];
                $mut_friend_name = $user_obj->get_name_from_handle($mutual_friends[$i]);
                $mut_friend_pic = $user_obj->get_profile_pic_from_handle($mutual_friends[$i]);
                $mut_friend_time = $this->calculate_time($this->get_friendship_time($this->user_handle, $mut_friend_handle));

                $str .= "
                
                    <a href='$mut_friend_handle' style='padding-right:1%;'><img class='mut-friend-pic' src='$mut_friend_pic'/></a>
                    <div class='mut-friend-content'>
                        <div class='mut-friend-name'><a href='$mut_friend_handle'>$mut_friend_name</a></div>
                        <div class='mut-friends-since' ><span style='font-weight:bold;'>Added:</span> $mut_friend_time</div>
                    </div>";
                    if($i > 0) {
                        $str.="<hr style='border-top: 1px solid black'>";
                    }
            }
            
            if($x < count($user_friend_array_iteration) - 1) {
                $str .= "</div>
                </div><hr style='border-top: 1px solid black'>";
            }

            
        }

        echo $str;
    }

    /**
     * Helper method to find mutual friends
     * @param user_handle: the user's handle
     * @param user_friend_list: the user's friend list; redundant, but saves time from re-querying
     * @param friend_handle: the friend's handle
     */
    public function find_mutual_friends($user_friend_list, $friend_handle) {

        $friend_friends = mysqli_query($this->con, "SELECT * FROM ((SELECT * FROM friend WHERE sender_handle='$friend_handle') 
                    UNION (SELECT * FROM friend WHERE receiver_handle='$friend_handle')) AS SUBQUERY WHERE accepted='y';");
        $mutual_friends = array();

        //going through all of friend's friends
        while($friend_row = mysqli_fetch_array($friend_friends)) {

            if(strcmp($friend_row["sender_handle"], $friend_handle) == 0) {
                $friend_friend_handle = $friend_row["receiver_handle"];
            }
            else {
                $friend_friend_handle = $friend_row["sender_handle"];
            }

            //going through all of user's friends
            for($y = 0; $y < count($user_friend_list); $y++) {
                $other_row = $user_friend_list[$y];

                if(strcmp($other_row["sender_handle"], $this->user_handle) == 0) {
                    $other_friend_handle = $other_row["receiver_handle"];
                }
                else {
                    $other_friend_handle = $other_row["sender_handle"];
                }

                //if user and friend have a common friend, add to returner array
                if(strcmp($friend_friend_handle, $other_friend_handle) == 0) {
                    array_push($mutual_friends, $other_friend_handle);
                }

            }

        }

        return $mutual_friends;
    }

    /**
     * Used for the search form. Here, user_friend_list is simply the array of handle strings
     */
    public function find_mutual_friends_calculator($user_friend_list, $friend_handle) {

        $friend_friends = mysqli_query($this->con, "SELECT * FROM ((SELECT * FROM friend WHERE sender_handle='$friend_handle') 
                    UNION (SELECT * FROM friend WHERE receiver_handle='$friend_handle')) AS SUBQUERY WHERE accepted='y';");
        $mutual_friends = array();

        //going through all of friend's friends
        while($friend_row = mysqli_fetch_array($friend_friends)) {

            if(strcmp($friend_row["sender_handle"], $friend_handle) == 0) {
                $friend_friend_handle = $friend_row["receiver_handle"];
            }
            else {
                $friend_friend_handle = $friend_row["sender_handle"];
            }

            //going through all of user's friends
            for($y = 0; $y < count($user_friend_list); $y++) {
                $other_row = $user_friend_list[$y];

                if(strcmp($other_row, $this->user_handle) == 0) {
                    $other_friend_handle = $other_row;
                }
                else {
                    $other_friend_handle = $other_row;
                }

                //if user and friend have a common friend, add to returner array
                if(strcmp($friend_friend_handle, $other_friend_handle) == 0) {
                    array_push($mutual_friends, $other_friend_handle);
                }

            }

        }

        return $mutual_friends;
    }

    public function get_raw_friend_list($accepted) {
       $friend_list = mysqli_query($this->con, "SELECT * FROM (((SELECT * FROM friend WHERE sender_handle='$this->user_handle') UNION 
       (SELECT * FROM friend WHERE receiver_handle='$this->user_handle'))) AS SUBQUERY WHERE accepted='$accepted' ORDER BY time DESC");

        $array_friend_list = array();
        while($row = mysqli_fetch_array($friend_list)) {
            array_push($array_friend_list, $row);
        }

        return $array_friend_list;
    }

    public function get_friendship_time($user1, $user2) {
        $friend_info = mysqli_fetch_array(mysqli_query($this->con, "(SELECT * FROM friend WHERE sender_handle='$user1' AND receiver_handle='$user2')
        UNION (SELECT * FROM friend WHERE sender_handle='$user2' AND receiver_handle='$user1');"));
        return $friend_info["time"];
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