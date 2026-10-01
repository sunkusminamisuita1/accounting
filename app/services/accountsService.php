<?php
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
// app/services/authService.php
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH.'/app/repositories/accountsRepository.php';
require_once ROOT_PATH.'/app/dto/accountsDto.php';
require_once ROOT_PATH.'/app/validators/accountsValidator.php';


class accountsService{

    public accountsValidator    $vali;
    public accountsRepository   $repo;
    private $pdo;
    private $dto;
    //private array $orgAcctAltTbl;

    public function __construct(accountsDto $dto, $pdo)    {
        $this->pdo      =   $pdo;
        $this->dto      =   $dto;
        
        $this->repo =   new accountsRepository($this->dto, $this->pdo);
        $this->vali =   new accountsValidator($this->dto, $this->pdo, $this->repo, false);
        $this->dto->accountsTypeTbl = $this->repo->getAccountsType();
        //var_dump($this->dto->accountsTypeTbl);exit;
    }

    public function getAccounts(){

        $this->dto->accounts  =   $this->repo->getAccounts(true);
        $this->dto->acctAltTbl = $this->dto->accounts;         //修正用科目テーブル作成

        foreach($this->dto->acctAltTbl as $key=>$row){   //errmsgカラム追加,初期化
            
            $this->dto->acctAltTbl[$key]['errmsg'] = '';
            $this->dto->acctAltTbl[$key]['editType'] = '';//初期値セット
            if(isset($row['is_deleted']) && $row['is_deleted'] ?? 0) {
                $this->dto->acctAltTbl[$key]['errmsg'] = "この勘定科目は削除済みです。";
                $this->dto->acctAltTbl[$key]['editType'] = "削除";
            }
         
        }
        unset($row);
    }

    public function accountsEdit(){
        $delKeys = [];
        foreach( $this->dto->postDt['acctUpdDt'] as $key=>$row){
            if($row['del'] ?? ''){
                $this->dto->acctAltTbl[$key]['editType'] = '削除';
                $this->dto->acctAltTbl[$key]['errmsg'] = '削除済み';
                $this->dto->acctAltTbl[$key]['is_deleted'] = 1;
            }else{
                $this->dto->acctAltTbl[$key]['editType'] = '更新';
                $this->dto->acctAltTbl[$key]['errmsg'] = '';
                $this->dto->acctAltTbl[$key]['is_deleted'] = 0;
            }
        }
        
    }

    public function accountsDlt(){

        $this->dto->acctAltTbl  =   $this->acctAltTblMake($this->dto->postDt['acctUpdDt']);
        $err = $this->vali->accountsVali();
        if ($err > 0) {
            unset($_SESSION['acctAltTbl']);
            return $err;
        }
        $wkAcctAltTbl = [];
        foreach( $this->dto->postDt['acctUpdDt'] as $key=>$row){

            if( ( $row['del'] ?? '' ) && ( $this->dto->postDt['AcctPfm'] === '削除' ?? '' ) ){
                $this->repo->acctDlt($key);
            }else{
                $wkAcctAltTbl['$key'] = $row;
            }
            $this->dto->acctAltTbl = $wkAcctAltTbl;
        }

        return 0;
    }

    public function accountsAdd(){
        $this->dto->acctAltTbl = [];
        $this->dto->acctAltTbl  =   $this->acctAltTblMake($this->dto->postDt['acctUpdDt']??[]);

        $_SESSION['tempNewId'] =  ($_SESSION['tempNewId'] ?? 0 ) - 1 ;

        $userId = $this->dto->id;
        if (!is_array($this->dto->postDt['acctUpdDt'] ?? null)) {
            $this->dto->postDt['acctUpdDt'] = [];
        }
        array_unshift($this->dto->acctAltTbl,['id'=> $_SESSION['tempNewId'], 'user_id'=>(int)$userId, 
                                              'shop_code'=>(string)($this->dto->shopCode ?? ''),
                                              'name'=>(string)($this->dto->postDt['acctUpdDt'][0]['name'] ?? '') ,
                                              'type'=>(string)($this->dto->postDt['acctUpdDt'][0]['type'] ?? ''), 
                                              'sort_order'=>(string)($this->dto->postDt['acctUpdDt'][0]['sort_order'] ?? ''),
                                              'errmsg'=>'', 
                                              'editType'=>'追加', 
                                              'is_deleted'=> 0 
                                          ]);

        return $this->dto->acctAltTbl;
    }

    public function repoDataMake(){

        foreach($this->dto->postDt['acctUpdDt'] ?? [] as $postKey=>$postRow){
 
            foreach($this->dto->acctAltTbl as $altKey => $altRow){
                if(  (int)$postRow['id']        !==      (int)$altRow['id'] ){
                    continue;
                } 
                $isAlt = (
                            ($postRow['name'] ?? null         !==     ($altRow['name'] ?? null))        || 
                            ($postRow['type'] ?? null)        !==     ($altRow['type'] ?? null)         ||
                            ((int)$postRow['sort_order'] ?? 0 !==     (int)$altRow['sort_order'] ?? 0)  ||
                            ((int)$postRow['del'] ?? 0        !==     (int)$altRow['is_deleted'] ?? 0)
                         );

                if( $isAlt ){
                    //echo "isalt";
                    if(($this->dto->postDt['acctUpdDt'][$postKey]['editType'] ?? '') !== '追加'){
                        //echo "更新";
                        $this->dto->postDt['acctUpdDt'][$postKey]['editType'] = '更新';
                    }
                }
            }
            //echo "ggggggg/{$this->dto->postDt['acctUpdDt'][$postKey]['editType']}";exit;

        }
        $this->dto->acctAltTbl  =   $this->acctAltTblMake($this->dto->postDt['acctUpdDt']);
        $this->accountsAlt();
        return $this->dto->acctAltTbl;
    }

    public function accountsAlt(){

        $err = $this->vali->accountsVali();
        if ($err > 0) {
            unset($_SESSION['acctAltTbl']);
            return;
        }

        // 画面全体の操作が「削除ボタンのクリック」だったかを変数に持っておく
        //$isDeleteAction = (($this->dto->postDt['AcctPfm'] ?? '') === '削除');

        foreach ($this->dto->acctAltTbl as $key => $row) {
            
            // 💡 1行ごとに「削除」の条件を満たしているかチェック
            // （前段の repoDataMake で is_deleted に '1' や 1 が入っていると仮定）
            // if ($isDeleteAction && ($row['is_deleted'] === 1 ) ) {
            //     $this->repo->acctDlt( $key);
            //     continue; // 削除した行は、追加や修正のチェックをスキップして次の行へ
            // }
            //echo "dddd{$row['editType']}";var_dump($row);
            switch ($row['editType']) {
                case '追加':
                    $this->repo->acctAdd($key);
                    break;

                case '更新':
                    //echo "xxxxxxxxxxxxxxxxxxxxxx";exit;
                case '削除':
                    $this->repo->acctEdit($key);
                    break;
            }
        }
    }

    public function accountsCancel(){    //修正データをもとに戻す
        unset($this->dto->acctAltTbl);
        $this->dto->acctAltTbl = $this->dto->accounts;

        //＃＃＃＃＃＃＃＃＃　　　cancel用repository作成必要　　　＃＃＃＃＃＃＃＃＃＃＃＃

    }

    public function acctAltTblMake(array $postRows){    //修正データをもとに戻す
        $acctAltTbl =[];

        foreach ($postRows as $key => $postRow) {
            $acctAltTbl[$key]['id']          = (int)($postRow['id'] ?? null);
            $acctAltTbl[$key]['user_id']     = (int)($postRow['user_id'] ?? null);
            $acctAltTbl[$key]['shop_code']   = (string)($this->dto->shopCode ?? '');
            $acctAltTbl[$key]['name']        = (string)($postRow['name'] ?? '');
            $acctAltTbl[$key]['type']        = (string)($postRow['type'] ?? '');
            $acctAltTbl[$key]['sort_order']  = (string)($postRow['sort_order'] ?? '');
            $acctAltTbl[$key]['errmsg']      = (string)($postRow['errmsg'] ?? '');
            $acctAltTbl[$key]['editType']    = (string)($postRow['editType'] ?? '');
            $acctAltTbl[$key]['is_deleted']  = (int)($postRow['del'] ?? 0);
        }
        return $acctAltTbl;
    }

}