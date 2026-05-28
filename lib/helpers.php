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
        echo "vvvvvvvvvvvvvvvvvvvvvvvvvvv";
        $ErrMsg = '11';
                    echo "<script type='text/javascript'>
                        alert('". h($ErrMsg) ."');
                      </script>";
        if(!empty($Dto) && !empty($Dto->ErrData)){
            foreach ($Dto->ErrData as $mod => $ErrData) {
                 $ErrMsg .= $mod . ": " . $ErrData . '\n';
            }
        }else{
            $ErrMsg = 'Program Error lib/helpers.php Dtoが空です。';            
        }

        if(!empty($ErrMsg)){
//            echo "<script type='text/javascript'>
//                        alert('". h($ErrMsg) ."');
//                        window.location.href = 'index.php?route=login';
//                      </script>";
            echo "<script type='text/javascript'>
                        alert('". h($ErrMsg) ."');
                      </script>";
            return 1;
        }
        return null;
    }
}