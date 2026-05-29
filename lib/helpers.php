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
    //$dto->ErrData['VoucherDto'] = '借方と貸方が一致しません';
    if(!empty(VoucherDTO->ErrData)){
        //$ErrMsg = $VoucherDto->ErrData['VoucherDto'];
        $ErrMsg = implode('\n', $ErrMsg->ErrData);
    }
    $ErrMsg = $ErrMsg ?? '';
    if (!empty($ErrMsg)) {
        echo "<script type='text/javascript'>
                    alert('". h($ErrMsg) ."');
                    window.location.href = 'index.php?route=login';
                  </script>";


        return 1;
    }
    return null;
   
}

class ErrMsgPopUp
{
    //    public function __construct($Dto)  {
    //    }
    public  function Show($Dto)

    {
        file_put_contents('/tmp/debug.log', "メソッド通ったよ！\n", FILE_APPEND);


        $ErrMsg = '';

        if(empty($Dto)){
            $ErrMsg = 'Program Error lib/helpers.php Dtoが空です。';            
        }else{
            if(!empty($Dto->ErrData)){
                foreach($Dto->ErrData as $key => $value){
                    $ErrMsg .= " . $value ";
                }
            }
        }

        if(!empty($ErrMsg)){
            //echo "<script type='text/javascript'>
            //            alert('". h($ErrMsg) ."');
            //          </script>";
            return "<script type='text/javascript'>
                        alert('". addslashes($ErrMsg) ."');
                      </script>";
        }
        return null;
    }
}