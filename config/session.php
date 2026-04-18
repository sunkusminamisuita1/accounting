<?php
//require_once __DIR__.'/../config/bootstrap.php';
echo "2.5".$_SESSION['ErrMsg']."<br>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "3".$_SESSION['ErrMsg']."<br>";
if(isset($_SESSION['ErrMsg'])) {
    return DispErrorMsg();
}
return 0;
?>
