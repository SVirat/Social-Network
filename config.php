<?php
ob_start();
session_start();
$timezone = date_default_timezone_set("Asia/Calcutta");

$con = mysqli_connect("localhost", "root", "", "user");

if(mysqli_connect_errno()) {
    echo "Failure: " . mysqli_connect_errno();
}

?>