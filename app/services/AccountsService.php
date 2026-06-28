<?php
// app/services/AuthService.php
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH.'/app/repositories/AccountsRepository.php';
require_once ROOT_PATH.'/app/DTO/AccountsDto.php';

class AccountsService{

    public AccountsValidator    $SvcVali;
    public AccountsRepository   $SvcRepo;
    public AccountsDto          $CtrDto;

    public function __construct(AccountsDto $Dto)    {
        $this->SvcRepo =   new AccountsRepository($Dto);
    }

    public function GetAccounts( AccountsDto $Dto){

        $Dto->Accounts  =   $this->SvcRepo->getAccounts();
        
        $Dto->AcctAltTbl = $Dto->Accounts;         //修正用科目テーブル作成

        foreach($Dto->AcctAltTbl as $key=>$Row){   //errmsgカラム追加,初期化
            $Dto->AcctAltTbl[$key]['errmsg'] = 'xxxx';
        }
        unset($Row);
        echo "<br><pre>" .var_dump($Dto->AcctAltTbl) . "</pre>";
    }

    public function AccountsDlt(AccountsDto $Dto){
        //echo "<br><pre>" .var_dump($Dto->AcctAltTbl) . "</pre>";


        $DelKeys = [];
        foreach( $_POST['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
            if($Row['del'] ?? ''){
                $DelKeys[] =  $Key;
            }            
        }
        rsort($DelKeys);

        foreach($DelKeys as $DelKey){
            array_splice($Dto->AcctAltTbl,(int)$DelKey,1);
        }
        
    }

    public function AccountsAdd(AccountsDto $Dto){

        $UserId = $Dto->id;
        array_unshift($Dto->AcctAltTbl,['id'=> null,'user_id'=>(int)$UserId,'name'=>'','type'=>'', 'errmsg'=>'']);

    }

    public function AccountsAlt(AccountsDto $Dto){

        foreach($Dto->AcctAltTbl as $key=>$Row){
            var_dump($Row); echo "Alt <br>";
        }
        $this->SvcVali->AccountsVali($this->CtrDto);

    }

    public function AccountsCancel(AccountsDto $Dto){    //修正データをもとに戻す

        $Dto->AcctAltTbl = $Dto->Accounts;

    }

}