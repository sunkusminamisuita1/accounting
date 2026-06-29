<?php
// app/services/AuthService.php
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH.'/app/repositories/AccountsRepository.php';
require_once ROOT_PATH.'/app/DTO/AccountsDto.php';

class AccountsService{

    public AccountsValidator    $SvcVali;
    public AccountsRepository   $SvcRepo;
    //public AccountsDto          $CtrDto;

    public function __construct(AccountsDto $Dto)    {
        $this->SvcRepo =   new AccountsRepository($Dto);
        $this->Svcvali =   new AccountsValidator($Dto);
    }

    public function GetAccounts( AccountsDto $Dto){

        $Dto->Accounts  =   $this->SvcRepo->getAccounts($Dto);
        
        $Dto->AcctAltTbl = $Dto->Accounts;         //修正用科目テーブル作成

        foreach($Dto->AcctAltTbl as $key=>$Row){   //errmsgカラム追加,初期化
            $Dto->AcctAltTbl[$key]['errmsg'] = 'xxxx';
        }
        unset($Row);
    }

    public function AccountsEdit(AccountsDto $Dto){
        //echo "<br><pre>" .var_dump($Dto->AcctAltTbl) . "</pre>";

        $DelKeys = [];
        foreach( $Dto->PostDt['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
            if($Row['del'] ?? ''){
                $Dto->AcctAltTbl[$Key]['edittype'] = '削除';
            }else{
                $Dto->AcctAltTbl[$Key] = $Row;
                $Dto->AcctAltTbl[$Key]['edittype'] = '更新';
            }            
        }
        
    }

    public function AccountsAdd(AccountsDto $Dto){

        $UserId = $Dto->id;
        array_unshift($Dto->AcctAltTbl,['id'=> null,'user_id'=>(int)$UserId,'name'=>'','type'=>'', 'errmsg'=>'', 'edittype'=>'追加']);

    }

    public function AccountsAlt(AccountsDto $Dto){

        $this->SvcVali->AccountsVali($Dto);

        foreach($Dto->AcctAltTbl as $Key=>$Row){
            switch($Row['edittype']){
                case '追加':
                    $Svc->Repo->AcctAdd($Dto,$Key);
                    break;
                case '更新':
                    $Svc->Repo->AcctEdit($Dto,$Key);
                    break;
                case '削除':
                    $Svc->Repo->AccotDlt($Dto,$Key);
                    break; 
            }
        }
    }

    public function AccountsCancel(AccountsDto $Dto){    //修正データをもとに戻す

        $Dto->AcctAltTbl = $Dto->Accounts;

    }

}