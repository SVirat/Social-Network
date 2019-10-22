<?php
include("config.php");
include("inc/classes/User.php");

$query = $_POST["query"];
$user_handle = $_POST["user_handle"];

$names = explode(" ", $query);

if(count($names) > 1) {
    $users_returned = mysqli_query($con, "SELECT * FROM user WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%' OR handle LIKE '$query%') AND deactivated='n' LIMIT 4;");
}
else if(count($names) == 1) {
    $users_returned = mysqli_query($con, "SELECT * FROM user WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%' OR handle LIKE '$query%') AND deactivated='n' LIMIT 4;");
}

if($query != "") {
    $num_users = mysqli_num_rows($users_returned);
    $count = 0;
    while($row = mysqli_fetch_array($users_returned)) {
        $count++;
        $user_obj = new User($con, $user_handle);
        $friend_obj = new Friend($con, $user_handle);

        if($row["handle"] != $user_handle) {
            $user_friends = $user_obj->get_friend_list();
            $num_mutual_friends = count($friend_obj->find_mutual_friends_calculator($user_friends, $row["handle"])) . " mutual friends";
        }
        else {
            $num_mutual_friends = "";
        }

        $other_handle = $row['handle'];
        $other_profile_pic = $row['profile_pic'];
        $other_name = $row["first_name"] . " " . $row["last_name"];

        echo "<div class='resultDisplay' style='background:#F2F3F4;'>
                <a href='$other_handle' style='color:#1485BD;width:90%'>
                    <div class='liveSearchProfilePic'>
                        <img src='$other_profile_pic' style='height:40px;width:40px;border-radius:50%;float: left;'>
                    </div>
                    
                    <div class='liveSearchText'>
                        <span style='float:left;margin-left:3%;'>$other_name</span><br>
                        <span style='font-size:11px;float:left;margin-left:3%;'>Handle: <i>$other_handle</i></span><br>
                        <span style='font-size:11px;text-align:center;margin-right:15%;color:grey'>$num_mutual_friends</span>
                    </div>
                </a>
            </div>";
        if($count < $num_users) {
        echo "<hr style='border-top: 1px solid black;padding: 0px;margin: 0px;'>";
        }

    }
}


?>