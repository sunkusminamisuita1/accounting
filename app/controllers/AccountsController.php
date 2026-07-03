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
        if( ! $this->CtrDto->Accounts){
            $this->CtrSvc->GetAccounts($this->CtrDto);
        }

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            $this->CtrDto->PostDt = $_POST;
            $ViewEditKey = $_POST['ViewEditKey'] ?? null;
            switch($_POST['AcctPfm']){

                case '追加':
                    $this->RestoreEditingData($this->CtrDto);
                    $this->CtrSvc->AccountsAdd($this->CtrDto);
                    $this->PrepareNextRequest($this->CtrDto);
                    break;

                case '削除':
                    $this->RestoreEditingData($this->CtrDto);
                    $this->CtrSvc->AccountsEdit($this->CtrDto,$ViewEditKey);
                    $this->PrepareNextRequest($this->CtrDto);
                    break;

                case '修正実行':                    
                    $this->RestoreEditingData($this->CtrDto);
                    $this->CtrSvc->RepoDataMake($this->CtrDto);
                    //echo "<br><pre>" . var_dump($this->CtrDto->Accounts) . "</pre><br><br>";
                    //echo "<br><pre>" . var_dump($this->CtrDto->AcctAltTbl) . "</pre><br><br>";
                    //exit;
                    //break;
                    $this->CtrSvc->AccountsAlt($this->CtrDto,$ViewEditKey);
                    $this->PrepareNextRequest($this->CtrDto);
                    break;

                case 'キャンセル':
                    $this->CtrSvc->AccountsCancel($this->CtrDto);
                    break;


            }
            
        }

            $TokenKey = generateCsrfToken();
            $Accounts   =   $this->CtrDto->AcctAltTbl;
        require ROOT_PATH.'/views/Accounts/AccountsView.php';
    }

    private function RestoreEditingData(AccountsDto $Dto){    //すでに修正データがある場合、編集データにコピー

        if($_SESSION['Accounts'] ?? ""){    
            $Dto->AcctAltTbl = $_SESSION['Accounts'];
            unset($_SESSION['Accounts']);
        }  

    }

    private function PrepareNextRequest(AccountsDto $Dto){    //次セッション、renderデータ準備
        $Dto->AcctAltTbl = array_values($Dto->AcctAltTbl); 
        $_SESSION['Accounts']   = $Dto->AcctAltTbl;
 
    }

}
?>