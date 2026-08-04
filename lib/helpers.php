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

function DispErrorMsg($errMsg)
{
    //$dto->ErrData['VoucherDto'] = '借方と貸方が一致しません';
    if(!empty(VoucherDto->ErrData)){
        //$errMsg = $VoucherDto->ErrData['VoucherDto'];
        $errMsg = implode('\n', $errMsg->ErrData);
    }
    $errMsg = $errMsg ?? '';
    if (!empty($errMsg)) {
        echo "<script type='text/javascript'>
                    alert('". h($errMsg) ."');
                    window.location.href = 'index.php?route=login';
                  </script>";


        return 1;
    }
    return null;
   
}

class errMsgPopUp
{
    //    public function __construct($dto)  {
    //    }
    public  function Show($dto)

    {
        file_put_contents('/tmp/debug.log', "メソッド通ったよ！\n", FILE_APPEND);


        $errMsg = '';

        if(empty($dto)){
            $errMsg = 'Program Error lib/helpers.php Dtoが空です。';            
        }else{
            if(!empty($dto->ErrData)){
                foreach($dto->ErrData as $key => $value){
                    $errMsg .= " . $value ";
                }
            }
        }

        if(!empty($errMsg)){
            //echo "<script type='text/javascript'>
            //            alert('". h($errMsg) ."');
            //          </script>";
            return "<script type='text/javascript'>
                        alert('". addslashes($errMsg) ."');
                      </script>";
        }
        return null;
    }
}