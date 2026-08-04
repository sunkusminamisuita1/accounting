<?php

require_once ROOT_PATH . '/app/services/accountsService.php';
require_once ROOT_PATH . '/app/dto/accountsDto.php';
require_once ROOT_PATH . '/lib/helpers.php';

class accountsController {
    Public        $ctrSvc;
    public        $ctrDto;
    public        $ctrerrMsgPopUp;

    public function __construct()
    {
        $this->ctrDto   =   new accountsDto();
        $this->ctrSvc   =   new accountsService($this->ctrDto);
        $this->ctrerrMsgPopUp = new errMsgPopUp($this->ctrDto);
    }

    public function index()
    {
        if( ! $this->ctrDto->accounts){
            $this->ctrSvc->getAccounts($this->ctrDto);
        }

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            $this->ctrDto->postDt = $_POST;
            $viewEditKey = $_POST['viewEditKey'] ?? null;
            switch($_POST['AcctPfm']){

                case '追加':
                    $this->restoreEditingData($this->ctrDto);
                    $this->ctrSvc->accountsAdd($this->ctrDto);
                    $this->prepareNextRequest($this->ctrDto);
                    break;

                case '削除':  //削除ボタンは、削除フラグのon offを切り替え,acctAltTblのis_deleted,errmsg,edittypeを更新
                    $this->restoreEditingData($this->ctrDto);
                    $this->ctrSvc->accountsEdit($this->ctrDto,$viewEditKey);
                    $this->prepareNextRequest($this->ctrDto);
                    break;

                case '修正実行':  //acctAltTblの内容をDBに反映する。                  
                    $this->restoreEditingData($this->ctrDto);
                    $this->ctrSvc->RepoDataMake($this->ctrDto);
                    //echo "<br><pre>" . var_dump($this->ctrDto->accounts) . "</pre><br><br>";
                    //echo "<br><pre>" . var_dump($this->ctrDto->acctAltTbl) . "</pre><br><br>";
                    //exit;
                    //break;
                    $this->ctrSvc->accountsAlt($this->ctrDto,$viewEditKey);
                    $this->prepareNextRequest($this->ctrDto);
                    break;

                case 'キャンセル':
                    $this->ctrSvc->accountsCancel($this->ctrDto);
                    break;


            }
            
        }

            $tokenKey = generateCsrfToken();
            $accounts   =   $this->ctrDto->acctAltTbl;
        require ROOT_PATH.'/views/accounts/accountsView.php';
    }

    private function restoreEditingData(accountsDto $dto){    //すでに修正データがある場合、編集データにコピー

        if($_SESSION['accounts'] ?? ""){    
            $dto->acctAltTbl = $_SESSION['accounts'];
            unset($_SESSION['accounts']);
        }  

    }

    private function prepareNextRequest(accountsDto $dto){    //次セッション、renderデータ準備
        //$dto->acctAltTbl = array_values($dto->acctAltTbl); 
        $_SESSION['accounts']   = $dto->acctAltTbl;
 
    }

}
?>