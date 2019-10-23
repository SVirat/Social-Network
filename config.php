<?php
ob_start();
session_start();
$timezone = date_default_timezone_set("Asia/Calcutta");

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$db = substr($url["path"], 1);

$con = mysqli_connect($server, $username, $password, $db);

if(mysqli_connect_errno()) {
    echo "Failure: " . mysqli_connect_errno();
}

?>
