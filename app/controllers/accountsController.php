<?php
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
require_once ROOT_PATH . '/app/services/accountsService.php';
require_once ROOT_PATH . '/app/dto/accountsDto.php';
require_once ROOT_PATH . '/lib/helpers.php';

class accountsController {
    Public        $service;
    public        $dto;
    public        $ctrerrMsgPopUp;
    private       $pdo;

    public function __construct($pdo)
    {
        $this->pdo          =   $pdo;
        $this->dto          =   new accountsDto();
        $this->service      =   new accountsService($this->dto,$this->pdo);
        $this->ctrerrMsgPopUp = new errMsgPopUp($this->dto);
    }

    public function index()
    {
        if( ! $this->dto->accounts){
            $this->service->getAccounts();
        }

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            $this->dto->postDt = $_POST;
            $viewEditKey = $_POST['viewEditKey'] ?? null;
            switch($_POST['AcctPfm']){

                case '追加':
                    $this->dto->acctAltTbl = $this->restoreEditingData();
                    $_SESSION['acctAltTbl'] = $this->service->accountsAdd();
                    break;

                case '削除':  //削除ボタンは、削除フラグのon の行をaccountsテーブルから削除する。
                    $this->dto->acctAltTbl = $this->restoreEditingData();
                    $err = $this->service->accountsDlt();
                    // if ($err) {
                    //     echo "<script>alert('削除できません。仕訳帳に使用されている勘定科目は削除できません。');</script>";
                    //     break;
                    // }
                    //unset($_SESSION['acctAltTbl']);
                    $this->service->getAccounts();
                    $_SESSION['acctAltTbl'] = $this->dto->acctAltTbl;
                    break;

                case '修正実行':  //acctAltTblの内容をDBに反映する。                  
                    $this->dto->acctAltTbl = $this->restoreEditingData();
                    //$err = $this->service->accountsDlt();
                    //if ($err) {
                    //    echo "<script>alert('削除できません。仕訳帳に使用されている勘定科目は削除できません。');</script>";
                    //    break;
                    //}
                    $gomi   =   $this->service->repoDataMake();
                    unset($_SESSION['acctAltTbl']);
                    //$this->service->getAccounts();
                    $_SESSION['acctAltTbl'] = $this->dto->acctAltTbl;
                    break;

                case 'キャンセル':
                    $this->service->accountsCancel();
                    break;

            }
            
        }

        $tokenKey = generateCsrfToken();
        unset($accounts);
        $accounts   =   $this->dto->acctAltTbl;
        require ROOT_PATH.'/views/accountsView.php';
    }

    private function restoreEditingData(){    //すでに修正データがある場合、編集データにコピー
        $acctAltTbl = $this->dto->acctAltTbl;
        if(isset($_SESSION['acctAltTbl'] )){ 
            $acctAltTbl = $_SESSION['acctAltTbl'];
            unset($_SESSION['acctAltTbl']);
        }
        return $acctAltTbl;

    }

    private function prepareNextRequest(){    //次セッション、renderデータ準備
 
    }

}
?>