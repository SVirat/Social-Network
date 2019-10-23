<?php include("inc/header.php"); 
include ("inc/classes/User.php");

if(isset($_GET["q"])) {
    $query = $_GET["q"];
}
else {
    $query = "";
}
?>

<div class="main-column" id="main-column">

    <?php
        if($query == "") {
            echo "Please enter something in the search box.";
        }
        else {
            $names = explode(" ", $query);

            if(count($names) > 1) {
                $users_returned = mysqli_query($con, "SELECT * FROM user WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%' OR handle LIKE '$query%') AND deactivated='n';");
            }
            else if(count($names) == 1) {
                $users_returned = mysqli_query($con, "SELECT * FROM user WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%' OR handle LIKE '$query%') AND deactivated='n';");
            }

            if(mysqli_num_rows($users_returned) <= 0) {
                echo "Cannot find any " . $query;
            }
            else {
                echo "<span style='margin-left:20%;'>Found " . mysqli_num_rows($users_returned) . " results:</span> <br><br>";

                echo "<div class='search-container' style='background:#F2F3F4;border-radius:10px;width:60%;margin-left:20%;'>";

                while($row = mysqli_fetch_array($users_returned)) {
                    $friend_obj = new Friend($con, $user["handle"]);
                    $button = "";
                    $mutual_friends = "";

                    if($user["handle"] != $row["handle"]) {
                        
                        if($friend_obj->is_friend($row["handle"])) {
                            $button = "<input type='submit' style='float:right;margin-right:10%;padding:20%;' name='" . $row["handle"] . "' class='extreme' value='Delete'>";
                        }
                        else if($friend_obj->sent_friend_request($row["handle"])) {
                            $button = "<input type='submit' style=';float:right;padding:20%;' name='" . $row["handle"] . "' class='extreme' value='Withdraw'>";
                        }
                        else if($friend_obj->received_friend_request($row["handle"])) {
                            $button = "
                            <input type='submit' class='safe' style='float:right;margin-right:2%;padding:20%;' name='add-" . $row["handle"] . "' value='Accept'><br><br><br>
                            <input type='submit' class='extreme' style='float:right;padding:20%;' name='del-" . $row["handle"] . "' value='Delete'>";                      
                        }
                        else {
                            $button = "<input type='submit' class='safe' style='float:right;padding: 20%;' name='" . $row["handle"] . "' value='Add Pal' >";
                        }

                        $user_obj = new User($con, $user["handle"]);
                        $user_friend_list = $user_obj->get_friend_list();
                        $mutual_friends = count($friend_obj->find_mutual_friends_calculator($user_friend_list, $row["handle"])) . " mutual friends";
                    }

                    //button forms
                    //for adding and deleting requests
                    if(isset($_POST[$row["handle"]])) {
                        if($friend_obj->is_friend($row["handle"])) {
                            $friend_obj->delete_friend($row["handle"]);
                        }
                        //not friends, so either request sent or add pal
                        else {
                            if($friend_obj->sent_friend_request($row["handle"])) {
                                $friend_obj->delete_friend($row["handle"]);
                            }
                            else {
                                $friend_obj->send_friend_request($row["handle"]);
                            }
                        }
                        header("Location: search.php?q=" . $query);
                        exit();
                    }
                    //for accepting a gotten request
                    if(isset($_POST["add-".$row["handle"]])) {
                        $friend_obj->accept_friend_request($row["handle"]);
                        header("Location: search.php?q=" . $query);
                        exit();
                    }
                    //for deleting a gotten request
                    if(isset($_POST["del-".$row["handle"]])) {
                        $friend_obj->delete_friend($row["handle"]);
                        header("Location: search.php?q=" . $query);
                        exit();
                    }


                    $other_handle = $row['handle'];
                    $other_profile_pic = $row['profile_pic'];
                    $other_name = $row["first_name"] . " " . $row["last_name"];

                    echo "<div class='search_result'>
                            <div class='searchPageFriendButtons' style='display:inline-block;float: right;margin-top: 10px;margin-right: 20%;'>
                                <form action='' method='POST'>$button<br></form>
                            </div>

                            <div class='result_profile_pic' style='display:inline-block;margin-left:10%;margin-right:10px;margin-top:2%;'>
                                <a href='profile.php?profile_handle=$other_handle'><img src='$other_profile_pic' style='height:100px;width:100px;border-radius:50%;'></a>
                            </div>

                            <div class='search-text-content' style='display:inline-block;margin-top:2%;'>
                            
                                <a href='profile.php?profile_handle=$other_handle'><b>$other_name</b><br>
                                    <span style='color:grey;'> Handle: ".$other_handle."</span>
                                </a><br>
                                $mutual_friends<br>
                            </div>
                            
                        </div><hr style='border-top:1px solid black;'>";

                }

                echo "</div>";
            
            }





        }


    ?>


</div>
