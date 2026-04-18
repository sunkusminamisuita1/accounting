<?php
//require_once __DIR__.'/../config/bootstrap.php';
$ErrMsg = $_SESSION['ErrMsg'] ?? "";
echo "2.5" . $ErrMsg . "<br>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$ErrMsg = $_SESSION['ErrMsg'] ?? "";
echo "3" . $ErrMsg . "<br>";
if (!empty($ErrMsg)) {
    return DispErrorMsg();
}
return 0;
?>
