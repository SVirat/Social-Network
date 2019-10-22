<?php include("inc/header.php"); 
include("inc/classes/User.php");

echo "<script>changeActive('messages')</script>";

$message_obj = new Message($con, $handle);

$new_user = "false";
if(isset($_GET["u"])) {
    $receiver_handle = $_GET["u"];
    $user_handle = $user["handle"];
    if($receiver_handle == "n") {
        $new_user = "true";
    }
}
else {
    $receiver_handle = $message_obj->get_most_recent_user();
    if($receiver_handle == "false") {
        $new_user = "true";
    }
}

if($new_user == "false") {
    $receiver_obj = new User($con, $receiver_handle);
}

if(isset($_POST["post_message"])) {
    if(isset($_POST["message_body"])) {
        $body = mysqli_real_escape_string($con, $_POST["message_body"]);
        $message_obj->send_message($receiver_handle, $body);
    }
    header("Location: messages.php?u=$receiver_handle");
    exit();
}

if(isset($_POST["new_messager"])) {
    $rec_han = $_POST["new_messager"];
    header("Location: messages.php?u=$rec_han");
    exit();
}

?>


<div id="centerer">

<div class="user-details" id="convos" style='display:inline-block;width:30%;vertical-align:top;border-right:1px solid black;overflow-y:scroll;height:70%;'>
    <a href="messages.php?u=n">New Message</a><hr>
        <div class="loaded-convos">
            <?php echo $message_obj->getConvos(); ?>
        </div>
        
    </div>

<div class="current-message">
    <?php
    if($new_user == "false") {
        $receiver_name = $receiver_obj->get_name();
        $receiver_prof_pic = $receiver_obj->get_profile_pic();
        // <a href='$poster_handle' style='float:left;padding-right:1%;'><img class='index-profile-pic' src='$poster_prof_pic'/></a>
        echo "<a href='$receiver_handle'><img class='message-pic' src='$receiver_prof_pic'></a>
                <div class='message-content'>";

        echo "<a href='$receiver_handle'><h4>$receiver_name</h4></a>";
        echo "<div class='loaded-messages' id='scroll-messages'>";
            echo $message_obj->get_messages($receiver_handle);
        echo "</div>";
    }
    else {
        echo "<h4 style='margin-left:10%;'>New Message</h4>";
    }
    ?>



    <div class="message-post" style='margin-left:10%;'>

        <form action="" method="POST">
            <?php
                if($new_user == "true") {
                    echo "Send message to: <input type='text' name='new_messager'>";
                    echo "<div class='results'></div>";
                }
                else {
                    echo "<textarea name='message_body' class='message-textarea' placeholder='Write a message...'></textarea>";
                    echo "<input type='submit' name='post_message' class='message-submit' id='message_submit' value='Send'>
                    </div> <!-- this div ends the message-content class -->
                    "; 
                }
            ?>
        </form>

    </div>

    </div>

    <script>
        var div = document.getElementById("scroll-messages");
        div.scrollTop = div.scrollHeight;

    </script>


</div>



</body>
</html>
