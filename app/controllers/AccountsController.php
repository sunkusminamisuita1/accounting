<?php

//                $_SESSION['user'] = [
//                    'id' => (int)$user['id'],
//                    'username' => $user['username'],
//                    'email' => $user['email'],
//                    'fiscalMonth' => $user['fiscal_month'],
//                    'fiscalDay' => $user['fiscal_day']
//                ];
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
            if($_POST['AcctPfm'] === "追加"){
                $this->CtrSvc->AccountsAdd($this->CtrDto);
            }
            if($_POST['AcctPfm'] === "削除"){
                $this->CtrSvc->AccountsDlt($this->CtrDto);
            }
            if($_POST['AcctPfm'] === "修正実行"){
                $this->CtrSvc->AccountsAlt($this->CtrDto);
            }
            if($_POST['AcctPfm'] === "キャンセル"){
                $this->CtrSvc->AccountsCancel($this->CtrDto);
            }

        }
            $TokenKey = generateCsrfToken();
            //$Accounts   =   $this->CtrDto->Accounts;
            $Accounts   =   $this->CtrDto->AcctAltTbl;
        require ROOT_PATH.'/views/Accounts/AccountsView.php';
    }
}





?>