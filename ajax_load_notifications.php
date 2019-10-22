<?php
include("config.php");
include("inc/classes/User.php");
include("inc/classes/Notification.php");

$limit = 10;

$notification = new Notification($con, $_REQUEST["user_handle"]);

echo $notification->getNotifications($_REQUEST, $limit);

?>