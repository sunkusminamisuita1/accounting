<?php
$ErrData = [];
for ($i = 0; $i < 5; $i++) {
    $ErrData[$i] = "エラー{$i}";
}
foreach ($ErrData as $errid => $errmsg) {
    echo $errid . ": " . $errmsg . "<br>";
}
exit;
?>