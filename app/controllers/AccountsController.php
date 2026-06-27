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
        $this->CtrSvc->GetAccounts($this->CtrDto);
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            if($_POST['AcctPfm'] === "追加"){
                echo "【AccountsAdd実行開始】";
                $this->CtrSvc->AccountsAdd($this->CtrDto);
                echo "【AccountsAdd実行終了】";
            }
            if($_POST['AcctPfm'] === "削除"){
                echo "【AccountsDlt実行開始】";
                $this->CtrSvc->AccountsDlt($this->CtrDto);
                echo "【AccountsDlt実行終了】";
            }
            if($_POST['AcctPfm'] === "修正実行"){
                echo "【AccountsAlt実行開始】";
                $this->CtrSvc->AccountsAlt($this->CtrDto);
                echo "【AccountsAlt実行終了】";
            }
        //    try {
        //        $CtrDto = new LoginDTO(
        //                            trim($_POST['email']),
        //                            $_POST['password']
        //                            );
        //        $user = $this->CtrSvc->login($dto);
        //        session_regenerate_id(true);
        //        $_SESSION['user'] = [
        //            'id' => (int)$user['id'],
        //            'username' => $user['username'],
        //            'email' => $user['email'],
        //            'fiscalMonth' => $user['fiscal_month'],
        //            'fiscalDay' => $user['fiscal_day']
        //        ];
        //        header('Location: index.php?route=home');
        //        exit;
        //    } 
        //    catch (Exception $e) {
        //        $message = $e->getMessage();
        //    }
        //    if($_POST['delete']){
        //        $this->CtrSvc->AccountsDlt;
        //    }
        }
            $TokenKey = generateCsrfToken();
            $Accounts   =   $this->CtrDto->Accounts;
        require ROOT_PATH.'/views/Accounts/AccountsView.php';
    }
}





?>