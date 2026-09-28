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

function dispErrorMsg($errMsg)
{
    //$dto->errData['voucherDto'] = '借方と貸方が一致しません';
    if(!empty(voucherDto->errData)){
        //$errMsg = $voucherDto->errData['voucherDto'];
        $errMsg = implode('\n', $errMsg->errData);
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

/**
 * デバッグログ出力用メソッド
 */


function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
    // デバッグモードがオフなら何もせず終了
    if (!$debugMode) {
        return;
    }

    $trace = debug_backtrace();
    
    // 呼び出し元の情報を取得
    $callerFile   = $trace[0]['file']     ?? '不明';
    $callerLine   = $trace[0]['line']     ?? '不明';
    $callerMethod = $trace[1]['function'] ?? '不明';
    $callerClass  = $trace[1]['class']    ?? '';
    
    $fileName = basename($callerFile);
    $location = " [{$fileName}:{$callerLine}] [{$callerClass}->{$callerMethod}]";

    // 1. 保存先のログファイルのパスを指定（例：同じディレクトリの debug.log）
    // ※環境に合わせて '/var/www/html/test6/logs/debug.log' など絶対パスでの指定が確実です
    $logFile = __DIR__ . '/debug.log';

    // 2. ログに書き出すテキスト（1行目）を組み立てる
    $logText = "[DEBUG]{$location} メッセージ: {$message}\n";

    // 3. 配列やオブジェクト（$data）がある場合は、テキストに変換して合体させる
    if ($data !== null) {
        // print_r の第2引数を true にすると、画面に出さず「文字列」として変数に代入できます
        $dataString = print_r($data, true);
        $logText .= "--- 付属データ ---\n" . $dataString . "-----------------\n";
    }

    // 4. ファイルへ書き出す（自動で日時のタイムスタンプが先頭に付きます）
    // 3番目の引数に「3」を指定すると、指定したファイルに「追記（末尾に足していく）」してくれます
    error_log($logText, 3, $logFile);
}




class errMsgPopUp
{
    private bool $debugMode = false;

    //    public function __construct($dto)  {
    //    }
    public  function show($dto)

    {
        file_put_contents('/tmp/debug.log', "メソッド通ったよ！\n", FILE_APPEND);


        $errMsg = '';

        if(empty($dto)){
            $errMsg = 'Program Error lib/helpers.php Dtoが空です。';            
        }else{
            if(!empty($dto->errData)){
                foreach($dto->errData as $key => $value){
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