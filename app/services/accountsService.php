<?php
// app/services/authService.php
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH.'/app/repositories/accountsRepository.php';
require_once ROOT_PATH.'/app/Dto/accountsDto.php';
require_once ROOT_PATH.'/app/Validators/accountsValidator.php';


class accountsService{

    public accountsValidator    $SvcVali;
    public accountsRepository   $SvcRepo;
    //public accountsDto          $ctrDto;

    public function __construct(accountsDto $Dto)    {
        $this->SvcRepo =   new accountsRepository($Dto);
        $this->SvcVali =   new accountsValidator('true');
    }

    public function GetAccounts( accountsDto $Dto){

        $Dto->accounts  =   $this->SvcRepo->getAccounts($Dto,true);
        //echo "<br><pre>" . var_dump($Dto->accounts) . "</pre><br>";

        $Dto->acctAltTbl = $Dto->accounts;         //修正用科目テーブル作成

        foreach($Dto->acctAltTbl as $key=>$Row){   //errmsgカラム追加,初期化
            $Dto->acctAltTbl[$key]['errmsg'] = '';
            $Dto->acctAltTbl[$key]['edittype'] = '更新';//初期値セット
            if(isset($Row['is_deleted']) && $Row['is_deleted'] ?? 0) {
                $Dto->acctAltTbl[$key]['errmsg'] = "この勘定科目は削除済みです。";
                $Dto->acctAltTbl[$key]['edittype'] = "削除";
            }
         
        }
        //echo "<br>xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx<br><pre>" .var_dump($Dto->accounts) . "</pre>yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy<br>";

        unset($Row);
    }

    public function accountsEdit(accountsDto $Dto){
        //echo "<br><pre>" .var_dump($Dto->acctAltTbl) . "</pre>";
        $DelKeys = [];
        foreach( $Dto->PostDt['AcctUpdDt'] as $Key=>$Row){
            if($Row['del'] ?? ''){
                $Dto->acctAltTbl[$Key]['edittype'] = '削除';
                $Dto->acctAltTbl[$Key]['errmsg'] = '削除済み';
                $Dto->acctAltTbl[$Key]['is_deleted'] = 1;
            }else{
                //$Dto->acctAltTbl[$Key] = $Row;
                $Dto->acctAltTbl[$Key]['edittype'] = '更新';
                $Dto->acctAltTbl[$Key]['errmsg'] = '';
                $Dto->acctAltTbl[$Key]['is_deleted'] = 0;
            }
        }
        
    }

    public function accountsAdd(accountsDto $Dto){

        $UserId = $Dto->id;
        array_unshift($Dto->acctAltTbl,['id'=> null,'user_id'=>(int)$UserId,'name'=>'','type'=>'', 'errmsg'=>'', 'edittype'=>'追加']);

    }

    public function RepoDataMake(accountsDto $Dto){

        foreach($Dto->PostDt['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
                $Dto->acctAltTbl[$Key]['id']        = $Dto->PostDt['AcctUpdDt'][$Key]['id'];
                $Dto->acctAltTbl[$Key]['user_id']   = $Dto->PostDt['AcctUpdDt'][$Key]['user_id'];
                $Dto->acctAltTbl[$Key]['name']      = $Dto->PostDt['AcctUpdDt'][$Key]['name'];
                $Dto->acctAltTbl[$Key]['type']      = $Dto->PostDt['AcctUpdDt'][$Key]['type'];
                //$Dto->acctAltTbl[$key]['errmsg']    = "このデータは削除済みです。";
                //$Dto->acctAltTbl[$key]['edittype']  = "削除";

                //if(!isset($Row['is_deleted']) && $Row['is_deleted'] ?? 0) {
                //    $Dto->acctAltTbl[$key]['errmsg'] = "このデータは削除済みです。";
                //    $Dto->acctAltTbl[$key]['edittype'] = "削除";
                //}
                //$Dto->PostDt['AcctUpdDt'][$Key]['del']= "on";
                //$Dto->acctAltTbl[$Key]['edittype']  = $Dto->PostDt['AcctUpdDt'][$Key]['edittype'] ?? '';
        }

    }

    public function accountsAlt(accountsDto $Dto){

        $Err = $this->SvcVali->accountsVali($Dto);
        if($Err > 0){
            return;
        }

        foreach($Dto->acctAltTbl as $Key=>$Row){

            switch($Row['edittype']){
                case '追加':
                    $this->SvcRepo->AcctAdd($Dto,$Key);
                    break;
                case '更新':
                    $this->SvcRepo->AcctEdit($Dto,$Key);
                    break;
                case '削除':
                    $this->SvcRepo->AcctDlt($Dto,$Key);
                    break;
                default:
                    echo "system error: edittype is not set.";
                    exit;
                    break;
            }
        }
    }

    public function accountsCancel(accountsDto $Dto){    //修正データをもとに戻す

        $Dto->acctAltTbl = $Dto->accounts;

    }

}