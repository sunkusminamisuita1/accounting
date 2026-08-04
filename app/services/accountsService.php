<?php
// app/services/authService.php
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH.'/app/repositories/accountsRepository.php';
require_once ROOT_PATH.'/app/dto/accountsDto.php';
require_once ROOT_PATH.'/app/Validators/accountsValidator.php';


class accountsService{

    public accountsValidator    $SvcVali;
    public accountsRepository   $SvcRepo;
    //public accountsDto          $ctrDto;

    public function __construct(accountsDto $dto)    {
        $this->SvcRepo =   new accountsRepository($dto);
        $this->SvcVali =   new accountsValidator('true');
    }

    public function GetAccounts( accountsDto $dto){

        $dto->accounts  =   $this->SvcRepo->getAccounts($dto,true);
        //echo "<br><pre>" . var_dump($dto->accounts) . "</pre><br>";

        $dto->acctAltTbl = $dto->accounts;         //修正用科目テーブル作成

        foreach($dto->acctAltTbl as $key=>$Row){   //errmsgカラム追加,初期化
            $dto->acctAltTbl[$key]['errmsg'] = '';
            $dto->acctAltTbl[$key]['edittype'] = '更新';//初期値セット
            if(isset($Row['is_deleted']) && $Row['is_deleted'] ?? 0) {
                $dto->acctAltTbl[$key]['errmsg'] = "この勘定科目は削除済みです。";
                $dto->acctAltTbl[$key]['edittype'] = "削除";
            }
         
        }
        //echo "<br>xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx<br><pre>" .var_dump($dto->accounts) . "</pre>yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy<br>";

        unset($Row);
    }

    public function accountsEdit(accountsDto $dto){
        //echo "<br><pre>" .var_dump($dto->acctAltTbl) . "</pre>";
        $DelKeys = [];
        foreach( $dto->postDt['AcctUpdDt'] as $Key=>$Row){
            if($Row['del'] ?? ''){
                $dto->acctAltTbl[$Key]['edittype'] = '削除';
                $dto->acctAltTbl[$Key]['errmsg'] = '削除済み';
                $dto->acctAltTbl[$Key]['is_deleted'] = 1;
            }else{
                //$dto->acctAltTbl[$Key] = $Row;
                $dto->acctAltTbl[$Key]['edittype'] = '更新';
                $dto->acctAltTbl[$Key]['errmsg'] = '';
                $dto->acctAltTbl[$Key]['is_deleted'] = 0;
            }
        }
        
    }

    public function accountsAdd(accountsDto $dto){

        $UserId = $dto->id;
        array_unshift($dto->acctAltTbl,['id'=> null,'user_id'=>(int)$UserId,'name'=>'','type'=>'', 'errmsg'=>'', 'edittype'=>'追加']);

    }

    public function RepoDataMake(accountsDto $dto){

        foreach($dto->postDt['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
                $dto->acctAltTbl[$Key]['id']        = $dto->postDt['AcctUpdDt'][$Key]['id'];
                $dto->acctAltTbl[$Key]['user_id']   = $dto->postDt['AcctUpdDt'][$Key]['user_id'];
                $dto->acctAltTbl[$Key]['name']      = $dto->postDt['AcctUpdDt'][$Key]['name'];
                $dto->acctAltTbl[$Key]['type']      = $dto->postDt['AcctUpdDt'][$Key]['type'];
                //$dto->acctAltTbl[$key]['errmsg']    = "このデータは削除済みです。";
                //$dto->acctAltTbl[$key]['edittype']  = "削除";

                //if(!isset($Row['is_deleted']) && $Row['is_deleted'] ?? 0) {
                //    $dto->acctAltTbl[$key]['errmsg'] = "このデータは削除済みです。";
                //    $dto->acctAltTbl[$key]['edittype'] = "削除";
                //}
                //$dto->postDt['AcctUpdDt'][$Key]['del']= "on";
                //$dto->acctAltTbl[$Key]['edittype']  = $dto->postDt['AcctUpdDt'][$Key]['edittype'] ?? '';
        }

    }

    public function accountsAlt(accountsDto $dto){

        $Err = $this->SvcVali->accountsVali($dto);
        if($Err > 0){
            return;
        }

        foreach($dto->acctAltTbl as $Key=>$Row){

            switch($Row['edittype']){
                case '追加':
                    $this->SvcRepo->AcctAdd($dto,$Key);
                    break;
                case '更新':
                    $this->SvcRepo->AcctEdit($dto,$Key);
                    break;
                case '削除':
                    $this->SvcRepo->AcctDlt($dto,$Key);
                    break;
                default:
                    echo "system error: edittype is not set.";
                    exit;
                    break;
            }
        }
    }

    public function accountsCancel(accountsDto $dto){    //修正データをもとに戻す

        $dto->acctAltTbl = $dto->accounts;

    }

}