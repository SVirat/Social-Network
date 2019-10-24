<?php 
require('vendor/autoload.php');
include ("inc/header.php");
include ("inc/classes/User.php");
// this will simply read AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY from env vars
$s3 = new Aws\S3\S3Client([
    'version'  => 'latest',
    'region'   => 'us-east-2',
]);
$bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');
$user_handle =  $user['handle'];
$target_dir = "assets/img/user_prof_pics/$user_handle/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
// Check if file already exists
/*
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
*/
// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        $upload = $s3->upload($bucket, $_FILES['fileToUpload']['name'], fopen($_FILES['fileToUpload']['tmp_name'], 'rb'), 'public-read');
        $user_obj = new User($con, $user_handle);
        $user_obj->set_profile_pic($upload->get('ObjectURL'));
        header("Location: settings.php");
        exit();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
