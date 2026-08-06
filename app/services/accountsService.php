<?php
// app/services/authService.php
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH.'/app/repositories/accountsRepository.php';
require_once ROOT_PATH.'/app/dto/accountsDto.php';
require_once ROOT_PATH.'/app/validators/accountsValidator.php';


class accountsService{

    public accountsValidator    $svcVali;
    public accountsRepository   $svcRepo;
    //public accountsDto          $ctrDto;

    public function __construct(accountsDto $dto)    {
        $this->svcRepo =   new accountsRepository($dto);
        $this->svcVali =   new accountsValidator('true');
    }

    public function getAccounts( accountsDto $dto){

        $dto->accounts  =   $this->svcRepo->getAccounts($dto,true);
        //echo "<br><pre>" . var_dump($dto->accounts) . "</pre><br>";

        $dto->acctAltTbl = $dto->accounts;         //修正用科目テーブル作成

        foreach($dto->acctAltTbl as $key=>$row){   //errmsgカラム追加,初期化
            $dto->acctAltTbl[$key]['errmsg'] = '';
            $dto->acctAltTbl[$key]['edittype'] = '更新';//初期値セット
            if(isset($row['is_deleted']) && $row['is_deleted'] ?? 0) {
                $dto->acctAltTbl[$key]['errmsg'] = "この勘定科目は削除済みです。";
                $dto->acctAltTbl[$key]['edittype'] = "削除";
            }
         
        }
        //echo "<br>xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx<br><pre>" .var_dump($dto->accounts) . "</pre>yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy<br>";

        unset($row);
    }

    public function accountsEdit(accountsDto $dto){
        //echo "<br><pre>" .var_dump($dto->acctAltTbl) . "</pre>";
        $delKeys = [];
        foreach( $dto->postDt['acctUpdDt'] as $key=>$row){
            if($row['del'] ?? ''){
                $dto->acctAltTbl[$key]['edittype'] = '削除';
                $dto->acctAltTbl[$key]['errmsg'] = '削除済み';
                $dto->acctAltTbl[$key]['is_deleted'] = 1;
            }else{
                //$dto->acctAltTbl[$key] = $row;
                $dto->acctAltTbl[$key]['edittype'] = '更新';
                $dto->acctAltTbl[$key]['errmsg'] = '';
                $dto->acctAltTbl[$key]['is_deleted'] = 0;
            }
        }
        
    }

    public function accountsAdd(accountsDto $dto){

        $userId = $dto->id;
        array_unshift($dto->acctAltTbl,['id'=> null,'user_id'=>(int)$userId,'name'=>'','type'=>'', 'errmsg'=>'', 'edittype'=>'追加']);

    }

    public function repoDataMake(accountsDto $dto){

        foreach($dto->postDt['acctUpdDt'] as $key=>$row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
                $dto->acctAltTbl[$key]['id']        = $dto->postDt['acctUpdDt'][$key]['id'];
                $dto->acctAltTbl[$key]['user_id']   = $dto->postDt['acctUpdDt'][$key]['user_id'];
                $dto->acctAltTbl[$key]['name']      = $dto->postDt['acctUpdDt'][$key]['name'];
                $dto->acctAltTbl[$key]['type']      = $dto->postDt['acctUpdDt'][$key]['type'];
                //$dto->acctAltTbl[$key]['errmsg']    = "このデータは削除済みです。";
                //$dto->acctAltTbl[$key]['edittype']  = "削除";

                //if(!isset($row['is_deleted']) && $row['is_deleted'] ?? 0) {
                //    $dto->acctAltTbl[$key]['errmsg'] = "このデータは削除済みです。";
                //    $dto->acctAltTbl[$key]['edittype'] = "削除";
                //}
                //$dto->postDt['acctUpdDt'][$key]['del']= "on";
                //$dto->acctAltTbl[$key]['edittype']  = $dto->postDt['acctUpdDt'][$key]['edittype'] ?? '';
        }

    }

    public function accountsAlt(accountsDto $dto){

        $err = $this->svcVali->accountsVali($dto);
        if($err > 0){
            return;
        }

        foreach($dto->acctAltTbl as $key=>$row){

            switch($row['edittype']){
                case '追加':
                    $this->svcRepo->acctAdd($dto,$key);
                    break;
                case '更新':
                    $this->svcRepo->acctEdit($dto,$key);
                    break;
                case '削除':
                    $this->svcRepo->acctDlt($dto,$key);
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