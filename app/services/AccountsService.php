<?php
// app/services/AuthService.php
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH.'/app/repositories/AccountsRepository.php';
require_once ROOT_PATH.'/app/DTO/AccountsDto.php';

class AccountsService{

    public AccountsValidator    $SvcVali;
    public AccountsRepository   $SvcRepo;
    public AccountsDto          $CtrDto;

    public function __construct(AccountsDto $CtrDto)    {
        $this->CtrDto   = $CtrDto;
        $this->SvcRepo =   new AccountsRepository($this->CtrDto);
    }

    public function GetAccounts(){

        $this->CtrDto->Accounts  =   $this->SvcRepo->getAccounts();
        //修正用科目テーブル作成
        $this->CtrDto->AcctAltTbl = $this->CtrDto->Accounts;

        }

    public function AccountsDlt(){

        if($_SESSION['Accounts'] ?? ""){    //すでに修正データがある場合、編集データにコピー
            $this->CtrDto->AcctAltTbl = $_SESSION['Accounts'];
            unset($_SESSION['Accounts']);
        }

        $DelKeys = [];
        foreach( $_POST['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
            if($Row['del'] ?? ''){
                $DelKeys[] =  $Key;
            }            
        }
        rsort($DelKeys);

        foreach($DelKeys as $Delkey){
            foreach($this->CtrDto->AcctAltTbl as $Key=>$Row){
                if((int)$Delkey === (int)$Key){
                    array_splice($this->CtrDto->AcctAltTbl,(int)$Key,1);
                }
            }
        }
        
        $this->CtrDto->Accounts = array_values($this->CtrDto->AcctAltTbl); 
        $_SESSION['Accounts']   = $this->CtrDto->Accounts;

    }

    public function AccountsAdd(){

        if($_SESSION['Accounts'] ?? ""){    //すでに修正データがある場合、編集データにコピー
            $this->CtrDto->AcctAltTbl = $_SESSION['Accounts'];
            unset($_SESSION['Accounts']);
        }

        $UserId = $_SESSION['user']['id'];
        array_unshift($this->CtrDto->AcctAltTbl,['id'=> null,'user_id'=>(int)$UserId,'name'=>'売上','type'=>'収益']);
        $this->CtrDto->Accounts = array_values($this->CtrDto->AcctAltTbl); 
        $_SESSION['Accounts']   = $this->CtrDto->Accounts;

    }

    public function AccountsAlt(){
        foreach($this->CtrDto->AcctAltTbl as $key=>$Row){
            var_dump($Row); echo "Alt <br>";
        }

    }

}