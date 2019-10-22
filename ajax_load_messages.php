<?php
include("config.php");
include("inc/classes/User.php");
include("inc/classes/Message.php");

$limit = 10;

$message = new Message($con, $_REQUEST["user_handle"]);

echo $message->getConvosDropdown($_REQUEST, $limit);

?>