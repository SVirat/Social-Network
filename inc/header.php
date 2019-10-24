<?php require "./config.php"; 
include("inc/classes/Notification.php");
include("inc/classes/Message.php");

if(isset($_SESSION["handle"])) {
    $handle = $_SESSION["handle"];
    $user = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM user WHERE handle='$handle'"));    
}
else {
    header("Location: door.php");
    exit();   
}

?>

<html>

<head>

    <!-- Outrun Fonts & Colors -->
    <link href="https://fonts.googleapis.com/css?family=Audiowide" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet"/>
    
    <!-- JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/header.js"></script>
    <script src="js/socialnetwork.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css"
         integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="./css/profile.css">
    <link rel="stylesheet" type="text/css" href="./css/index.css">
    <link rel="stylesheet" type="text/css" href="./css/friends.css">
    <link rel="stylesheet" type="text/css" href="./css/messages.css">
    <link rel="stylesheet" type="text/css" href="./css/settings.css">
    <link rel="stylesheet" type="text/css" href="./css/post.css">
    <link rel="stylesheet" type="text/css" href="./css/style.css">

    <title>PalWeb</title>

</head>

<body style="background-color: transparent;">

    <?php 
        $user_handle =  $user["handle"];
        $notification = new Notification($con, $user["handle"]);
        $num_notifications = $notification->get_unread_number();

        $message = new Message($con, $user["handle"]);
        $num_messages = $message->get_unread_number();

    ?>

    <div id="navbar">

        <div class="search" style='display:inline-block;position:absolute;margin-left:50%;vertical-align:center;'>
        <form action="search.php" method="GET" name="search_form" style="width:5px;">
            <input type="text"style='margin-top: 13px;border:1px solid blue;border-radius:5px;' onkeyup="getLiveSearchUsers(this.value, '<?php echo $user_handle; ?>');" name="q" placeholder="Search..." autocomplete="off" id="search_text_input">
            
            
        </form>
        <div class="search-button-holder" style="margin-top:-50px;margin-left:210px;background:white;border-radius:10px;cursor:pointer;"><i class="fa fa-search fa-lg" style="background:#F2F3F4;border-radius:5px;padding:4px;"></i></div>

        <div class="search_results" style="border: 1px solid black;border-bottom: 0;"></div>
        <div class="search_results_footer_empty" style="border: 1px solid black;background:#F2F3F4;"></div>

        </div>

        <div id="icons" class="icons" style='display:inline-block;'>
            <a href="profile.php?profile_handle=<?php echo $user['handle']; ?>" id="profile"><i class="fa fa-user fa-lg"></i></a>
            <a href="index.php" class="active" id="index"><i class="fa fa-home fa-lg"></i></a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $user_handle; ?>');" id="messages"><i class="fa fa-comments fa-lg"></i></a>
            <?php
                if($num_messages > 0) {
                    echo "<span class='message-badge' onclick='getDropdownData("."\"".$user_handle."\"".");' id='unread-messages'>" . $num_messages . "</span>";
                }
            ?>
            <a href="javascript:void(0);" onclick="getDropdownNotifications('<?php echo $user_handle; ?>');" id="notifications"><i class="fa fa-bell fa-lg"></i>
            <?php
                if($num_notifications > 0) {
                     echo "<span class='notification-badge' onclick='getDropdownNotifications(" . $user_handle . ");' id='unread-notifications'>" . $num_notifications . "</span>";
                }
            ?>
            </a>
            <a href="friends.php" id="friends"><i class="fa fa-users fa-lg"></i></a>
            <a href="door.php" id="logout" style="float:right"><i class="fa fa-times fa-lg"></i></i></a>
            <a href="settings.php" id="settings" style="float:right"><i class="fa fa-wrench fa-lg"></i></a>
        </div>

    </div>

    <div class="dropdown-data-window" style='position:absolute'></div>
    <div class="dropdown-data-window-notification" style='position:absolute'></div>
    <input type='hidden' id='dropdown-data-type' value="">

    <br>
