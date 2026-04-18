<?php
//require_once __DIR__.'/../config/bootstrap.php';
echo "2.5".$_SESSION['flash_message']."<br>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "3".$_SESSION['flash_message']."<br>";
if(isset($_SESSION['flash_message'])) {
    return DispErrorMsg();
}
return 0;
?>
