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
        foreach($this->CtrDto->Accounts as $key=>$Row){
            //echo "key={$key} = {$Row['id']}{$Row['name']} {$Row['type']}<br>";
        }
        //echo "xxxxx={$this->CtrDto->Accounts[1]['name']} <br>";
        //exit;
    }

    public function AccountsDlt(){

        if($_SESSION['Accounts'] ?? ""){    //すでに修正データがある場合、編集データにコピー
            $this->CtrDto->AcctAltTbl = $_SESSION['Accounts'];
        }




foreach( $_POST['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除idを保存
    echo "<br>key={$Key}  row="; var_dump($Row);
}
//exit;



        $DelKeys = [];
        foreach( $_POST['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除idを保存
            if($Row['del'] ?? ''){
                $DelKeys[] =  $Row['id'];
            }            
        }
echo "<br>"; var_dump($DelKeys);
//exit;

        foreach($this->CtrDto->AcctAltTbl as $Key=>$Row){

            foreach($DelKeys as $Idx => $DltId){
echo "<br>xxdltid={$DltId}   row={$Row['id']}";
                if($DltId === $Row['id']){
echo "<br>dltid={$DltId}   row={$Row['id']}";
                    array_splice($this->CtrDto->AcctAltTbl,$DltId,1);
                    break;
                }

                
            }            
        
        }
        $this->CtrDto->Accounts = array_values($this->CtrDto->AcctAltTbl); 
        $_SESSION['Accounts']   = $this->CtrDto->Accounts;

    }

    public function AccountsAdd(){
        foreach($this->CtrDto->AcctAltTbl as $key=>$Row){
            var_dump($Row); echo "Add <br>";
        }
        echo "【ループ終了】";
    }

    public function AccountsAlt(){
        foreach($this->CtrDto->AcctAltTbl as $key=>$Row){
            var_dump($Row); echo "Alt <br>";
        }

    }

}