<?php
function h(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function old(string $key, string $default = ''): string
{
    return h($_POST[$key] ?? $_GET[$key] ?? $default);
}
function requirePost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
}

function DispErrorMsg($ErrMsg)
{
    if (!empty($ErrMsg)) {

 


        echo "<script type='text/javascript'>
                    alert('". h($ErrMsg) ."');
                    window.location.href = 'index.php?route=login';
                  </script>";


        return 1;
    }
    return null;
   
}





//echo "<script type='text/javascript'>
//            alert('ログインしていません。ログイン画面へ戻ります。');
//            window.location.href = '/login.php';
//          </script>";
//    exit;
?>