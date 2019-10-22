<?php include("inc/header.php"); 
include("inc/classes/User.php"); //no need to include Friend.php as Users already includes Friend.php

echo "<script>changeActive('friends')</script>";

?>

<div id="wrapper">

    View <a href='pending_friends.php'>Pending Requests</a>

    <div id="friend-list">
        <?php 
            $friend_obj = new Friend($con,  $user["handle"]);
            $friend_obj->show_friends('y');
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