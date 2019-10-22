<?php 
include("config.php");
include("inc/classes/User.php");
include("inc/classes/Post.php");

$limit = 7;

$posts = new User($con, $_REQUEST["user_handle"]);
$posts->get_posts($_REQUEST, $limit);


?>