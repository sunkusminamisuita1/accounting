<?php

require_once ROOT_PATH . '/app/services/AccountsService.php';
require_once ROOT_PATH . '/app/DTO/AccountsDto.php';
require_once ROOT_PATH . '/lib/helpers.php';

class AccountsController {
    Public        $CtrSvc;
    public        $CtrDto;
    public        $CtrErrMsgPopUp;

    public function __construct()
    {
        $this->CtrDto   =   new AccountsDto();
        $this->CtrSvc   =   new AccountsService($this->CtrDto);
        $this->CtrErrMsgPopUp = new ErrMsgPopUp($this->CtrDto);
    }

    public function index()
    {
        echo "<br>【初期状態】Accounts=" . ($this->CtrDto->Accounts ? "あり" : "なし");
        if( ! $this->CtrDto->Accounts){
            echo "<br>【GetAccounts実行前】";
            $this->CtrSvc->GetAccounts($this->CtrDto);
            echo "<br>【GetAccounts実行後】";
            var_dump($this->CtrDto->AcctAltTbl[0] ?? 'なし');
        }


echo "<br>【REQUEST_METHOD】" . $_SERVER['REQUEST_METHOD'];
echo "<br>【POST内容】";
var_dump($_POST);


        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            switch($_POST['AcctPfm']){

                case '追加':
                    $this->RestoreEditingData($this->CtrDto);
                    $this->CtrSvc->AccountsAdd($this->CtrDto);
                    $this->PrepareNextRequest($this->CtrDto);
            echo "<br>【追加】";
            var_dump($this->CtrDto->AcctAltTbl[0] ?? 'なし');
                    break;

                case '削除':
                    $this->RestoreEditingData($this->CtrDto);
                    $this->CtrSvc->AccountsDlt($this->CtrDto);
                    $this->PrepareNextRequest($this->CtrDto);
            echo "<br>【削除】";
            var_dump($this->CtrDto->AcctAltTbl[0] ?? 'なし');
                    break;

                case '修正実行':
                    $this->RestoreEditingData($this->CtrDto);
                    $this->CtrSvc->AccountsAlt($this->CtrDto);
                    $this->PrepareNextRequest($this->CtrDto);
            echo "<br>【修正実行】";
            var_dump($this->CtrDto->AcctAltTbl[0] ?? 'なし');
                    break;

                case 'キャンセル':
                    $this->CtrSvc->AccountsCancel($this->CtrDto);
            echo "<br>{キャンセル】";
            var_dump($this->CtrDto->AcctAltTbl[0] ?? 'なし');
                    break;

            }
            
        }

            $TokenKey = generateCsrfToken();
            echo "<br>【ビュー渡前】AcctAltTblの最初のデータ:";
            var_dump($this->CtrDto->AcctAltTbl[0] ?? 'なし');
            $Accounts   =   $this->CtrDto->AcctAltTbl;
        require ROOT_PATH.'/views/Accounts/AccountsView.php';
    }

    private function RestoreEditingData(AccountsDto $Dto){    //すでに修正データがある場合、編集データにコピー

        if($_SESSION['Accounts'] ?? ""){    
            echo "xxxxxxxxxxxxxxxxxxxxxxxxxx";
            $Dto->AcctAltTbl = $_SESSION['Accounts'];
            unset($_SESSION['Accounts']);
        }  

    }

    private function PrepareNextRequest(AccountsDto $Dto){    //次セッション、renderデータ準備
        echo "<br>LAst<pre>" .var_dump($Dto->AcctAltTbl) . "</pre>";
        $Dto->AcctAltTbl = array_values($Dto->AcctAltTbl); 
        $_SESSION['Accounts']   = $Dto->AcctAltTbl;
 
    }

}





?>