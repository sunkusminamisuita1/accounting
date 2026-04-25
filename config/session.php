//<?php

echo "1" . "<br>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//$ErrMsg = $_SESSION['ErrMsg'] ?? "";
//echo "3" . $ErrMsg . "<br>";
//if (!empty($ErrMsg)) {
//    return DispErrorMsg($ErrMsg);
//    exit;
//}
//return 0;
//exit;
//?>
