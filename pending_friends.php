<?php include("inc/header.php"); 
include("inc/classes/User.php"); //no need to include Friend.php as Users already includes Friend.php

echo "<script>changeActive('friends')</script>";

?>

<div id="wrapper">

View <a href='friends.php'>Friends</a>

<div id='pending-requests'>
        <?php
            $user_handle = $user['handle'];
            $req_get = mysqli_query($con, "SELECT * FROM friend WHERE (receiver_handle='$user_handle' OR sender_handle='$user_handle') AND accepted='n';");
            $friend_obj = new Friend($con, $user_handle);
            $req_get_size = mysqli_num_rows($req_get);
            $num_its = 0;

            while($req = mysqli_fetch_array($req_get)) {
                if($num_its >= 1) {
                    break;
                }
                $num_its++;
                
                $raw_friend_list = $friend_obj->get_raw_friend_list('n');
                $actual_friend_list = $friend_obj->get_raw_friend_list('y');

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

                //going through user's friends list (currently N in database)
                for($x = 0; $x < count($user_friend_array_iteration) - 1; $x++ ) {

                    
                    
                    $row = $user_friend_array_iteration[$x];

                    //finding friend's handle
                    if(strcmp($row["sender_handle"], $user_handle) == 0) {
                        $friend_handle = $row["receiver_handle"];
                    }
                    else {
                        $friend_handle = $row["sender_handle"];
                    }

                    //since friend_handle is unique key, this will only return one row and there is on need for another while loop
                    $friend_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$friend_handle';"));

                    $friend_profile_pic = $friend_info["profile_pic"];
                    $friend_name = $friend_info["first_name"] . " " . $friend_info["last_name"];
                    $time_added = $friend_obj->calculate_time($row["time"]);

                    //finding mutual friends (CORRECT)
                    $mutual_friends = $friend_obj->find_mutual_friends($friend_friend_array_iteration_pending, $friend_handle);
                    
                    $num_mutual_friends = count($mutual_friends);
                    $mutual_friends_string = implode(', ', $mutual_friends);
                    
                    if(isset($_POST["remove_friend" . $friend_handle])) {
                        $user_handle = $user['handle'];
                        $remover_obj = new Friend($con, $user_handle);
                        $remover_obj->delete_friend($friend_handle);
                        header("Location: pending_friends.php");
                        exit();
                    }

                    if(isset($_POST["add_friend" . $friend_handle])) {
                        $user_handle = $user['handle'];
                        $remover_obj = new Friend($con, $user_handle);
                        $remover_obj->send_friend_request($friend_handle);
                        header("Location: pending_friends.php");
                        exit();
                    }

                    if(isset($_POST["accept_friend_req" . $friend_handle])) {
                        $user_handle = $user['handle'];
                        $remover_obj = new Friend($con, $user_handle);
                        $remover_obj->accept_friend_request($friend_handle);
                        header("Location: pending_friends.php");
                        exit();
                    }


                    echo
                    "<div class='friend'>
                        <a href='profile.php?profile_handle=$friend_handle' style='padding-right:1%;'><img class='friend-profile-pic' src='$friend_profile_pic'/></a>
                        <div class='friend-content'>
                            <div class='friend-name'><a href='profile.php?profile_handle=$friend_handle'>$friend_name</a></div>
                            <div class='friends-since' ><span style='font-weight:bold;'>Pending since:</span> $time_added</div><br>
                            <div class='mutual-friends' onclick='toggleMutualShower("."\"".$friend_handle."\"".");'><span style='font-weight:bold;cursor:pointer;color:blue' >Mutual friends:</span> $num_mutual_friends</div>
                            <form method='POST'>";
                            
                            ?>
                            <?php
                                if($friend_obj->is_friend($friend_handle)) {
                                    echo "<input type='submit' name='remove_friend$friend_handle' style='display:inline-block;float:right;' class='extreme' value='Remove' >";
                                }
                                else if($friend_obj->sent_friend_request($friend_handle)) {
                                    echo "<input type='submit' name='remove_friend$friend_handle'  style='display:inline-block;float:right;' class='extreme' value='Withdraw' >";
                                }
                                else if($friend_obj->received_friend_request($friend_handle)) {
                                    echo "<input type='submit' class='extreme' style='display:inline-block;float:right;' name='remove_friend$friend_handle' value='Delete'>";
                                    echo "<input type='submit' class='safe' style='display:inline-block;float:right;margin-right:2%;' name='accept_friend_req$friend_handle' value='Accept'>";
                                }
                                else {
                                    echo "<input type='submit' class='safe' style='display:inline-block;float:right;' name='add_friend$friend_handle' value='Add' >";
                                }
                            ?>
                            
                            
                            
                            
                        <?php
                        echo "</form>
                        </div><div class='mutual-friends-shower' id='$friend_handle'style='display:none;border-radius:10px;'>";

                    //going in reverse order of array, which means chronological order of friend addition
                    for($i = $num_mutual_friends - 1; $i >= 0; $i--) {
                        
                        $user_obj = new User($con, $mutual_friends[$i]);
                        $mut_friend_handle = $mutual_friends[$i];
                        $mut_friend_name = $user_obj->get_name_from_handle($mutual_friends[$i]);
                        $mut_friend_pic = $user_obj->get_profile_pic_from_handle($mutual_friends[$i]);
                        $mut_friend_time = $friend_obj->calculate_time($friend_obj->get_friendship_time($user_handle, $mut_friend_handle));

                       echo "
                        
                            <a href='profile.php?profile_handle=$mut_friend_handle' style='padding-right:1%;'><img class='mut-friend-pic' src='$mut_friend_pic'/></a>
                            <div class='mut-friend-content'>
                                <div class='mut-friend-name'><a href='profile.php?profile_handle=$mut_friend_handle'>$mut_friend_name</a></div>
                                <div class='mut-friends-since' ><span style='font-weight:bold;'>Added:</span> $mut_friend_time</div>
                            </div>";
                            if($i > 0) {
                                echo "<hr style='border-top: 1px solid black'>";
                            }
                    }
                    
                    if($x < count($user_friend_array_iteration) - 1) {
                       echo "</div>
                        </div><hr style='border-top: 1px solid black'>";
                    }

                    
                }

                //LAST ONE is added separately because of repeating pending_requests.php on mutual opener bug
                $x = count($user_friend_array_iteration) - 1;

                $row = $user_friend_array_iteration[$x];

                    //finding friend's handle
                    if(strcmp($row["sender_handle"], $user_handle) == 0) {
                        $friend_handle = $row["receiver_handle"];
                    }
                    else {
                        $friend_handle = $row["sender_handle"];
                    }

                    //since friend_handle is unique key, this will only return one row and there is on need for another while loop
                    $friend_info = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$friend_handle';"));

                    $friend_profile_pic = $friend_info["profile_pic"];
                    $friend_name = $friend_info["first_name"] . " " . $friend_info["last_name"];
                    $time_added = $friend_obj->calculate_time($row["time"]);

                    //finding mutual friends (CORRECT)
                    $mutual_friends = $friend_obj->find_mutual_friends($friend_friend_array_iteration_pending, $friend_handle);
                    
                    $num_mutual_friends = count($mutual_friends);
                    $mutual_friends_string = implode(', ', $mutual_friends);
                    
                    if(isset($_POST["remove_friend" . $friend_handle])) {
                        $user_handle = $user['handle'];
                        $remover_obj = new Friend($con, $user_handle);
                        $remover_obj->delete_friend($friend_handle);
                        header("Location: pending_friends.php");
                        exit();
                    }

                    if(isset($_POST["add_friend" . $friend_handle])) {
                        $user_handle = $user['handle'];
                        $remover_obj = new Friend($con, $user_handle);
                        $remover_obj->send_friend_request($friend_handle);
                        header("Location: pending_friends.php");
                        exit();
                    }

                    if(isset($_POST["accept_friend_req" . $friend_handle])) {
                        $user_handle = $user['handle'];
                        $remover_obj = new Friend($con, $user_handle);
                        $remover_obj->accept_friend_request($friend_handle);
                        header("Location: pending_friends.php");
                        exit();
                    }


                    echo
                    "<div class='friend'>
                        <a href='profile.php?profile_handle=$friend_handle' style='padding-right:1%;'><img class='friend-profile-pic' src='$friend_profile_pic'/></a>
                        <div class='friend-content'>
                            <div class='friend-name'><a href='profile.php?profile_handle=$friend_handle'>$friend_name</a></div>
                            <div class='friends-since' ><span style='font-weight:bold;'>Pending since:</span> $time_added</div><br>
                            <div class='mutual-friends' onclick='toggleMutualShower("."\"".$friend_handle."\"".");'><span style='font-weight:bold;cursor:pointer;color:blue' >Mutual friends:</span> $num_mutual_friends</div>
                            <form method='POST'>";
                            
                            ?>
                            <?php
                                if($friend_obj->is_friend($friend_handle)) {
                                    echo "<input type='submit' name='remove_friend$friend_handle' style='display:inline-block;float:right;' class='extreme' value='Remove' >";
                                }
                                else if($friend_obj->sent_friend_request($friend_handle)) {
                                    echo "<input type='submit' name='remove_friend$friend_handle'  style='display:inline-block;float:right;' class='extreme' value='Withdraw' >";
                                }
                                else if($friend_obj->received_friend_request($friend_handle)) {
                                    echo "<input type='submit' class='extreme' style='display:inline-block;float:right;' name='remove_friend$friend_handle' value='Delete'>";
                                    echo "<input type='submit' class='safe' style='display:inline-block;float:right;margin-right:2%;' name='accept_friend_req$friend_handle' value='Accept'>";
                                }
                                else {
                                    echo "<input type='submit' class='safe' style='display:inline-block;float:right;' name='add_friend$friend_handle' value='Add' >";
                                }
                            ?>
                            
                            
                            
                            
                        <?php
                        echo "</form>
                        </div><div class='mutual-friends-shower' id='$friend_handle'style='display:none;border-radius:10px;'>";

                    //going in reverse order of array, which means chronological order of friend addition
                    for($i = $num_mutual_friends - 1; $i >= 0; $i--) {
                        
                        $user_obj = new User($con, $mutual_friends[$i]);
                        $mut_friend_handle = $mutual_friends[$i];
                        $mut_friend_name = $user_obj->get_name_from_handle($mutual_friends[$i]);
                        $mut_friend_pic = $user_obj->get_profile_pic_from_handle($mutual_friends[$i]);
                        $mut_friend_time = $friend_obj->calculate_time($friend_obj->get_friendship_time($user_handle, $mut_friend_handle));

                       echo "
                        
                            <a href='profile.php?profile_handle=$mut_friend_handle' style='padding-right:1%;'><img class='mut-friend-pic' src='$mut_friend_pic'/></a>
                            <div class='mut-friend-content'>
                                <div class='mut-friend-name'><a href='profile.php?profile_handle=$mut_friend_handle'>$mut_friend_name</a></div>
                                <div class='mut-friends-since' ><span style='font-weight:bold;'>Added:</span> $mut_friend_time</div>
                            </div>";
                            if($i > 0) {
                                echo "<hr style='border-top: 1px solid black'>";
                            }
                    }


            }



        ?>
        <div id="show-mutual-friends" style="display:none;"></div>
    </div>
</div>
<br>

<script>
            function toggleMutualShower(fh) {
                var mutual_friends = document.getElementById(""+fh);
                if(mutual_friends.style.display == "block") {
                    mutual_friends.style.display = "none";
                }
                else {
                    mutual_friends.style.display = "block";
                }
            }
            </script>

</body>




</html>
