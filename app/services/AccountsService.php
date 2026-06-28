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
        //修正用科目テーブル作成
        $Dto->AcctAltTbl = $Dto->Accounts;

        }

    public function AccountsDlt(AccountsDto $Dto){

        //if($_SESSION['Accounts'] ?? ""){    //すでに修正データがある場合、編集データにコピー
        //    $Dto->AcctAltTbl = $_SESSION['Accounts'];
        //    unset($_SESSION['Accounts']);
        //}
        $this->AccountsDtChk($Dto);

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
        
        //$Dto->Accounts = array_values($Dto->AcctAltTbl); 
        //$_SESSION['Accounts']   = $Dto->Accounts;
        $this->AccountsNextDt($Dto);

    }

    public function AccountsAdd(AccountsDto $Dto){

        //if($_SESSION['Accounts'] ?? ""){    //すでに修正データがある場合、編集データにコピー
        //    $Dto->AcctAltTbl = $_SESSION['Accounts'];
        //    unset($_SESSION['Accounts']);
        //}
        $this->AccountsDtChk($Dto);

        $UserId = $Dto->id;
        array_unshift($Dto->AcctAltTbl,['id'=> null,'user_id'=>(int)$UserId,'name'=>'売上','type'=>'収益']);
        //$Dto->Accounts = array_values($Dto->AcctAltTbl); 
        //$_SESSION['Accounts']   = $Dto->Accounts;
        $this->AccountsNextDt($Dto);

    }

    public function AccountsAlt(AccountsDto $Dto){

        $this->AccountsDtChk($Dto);
        foreach($Dto->AcctAltTbl as $key=>$Row){
            var_dump($Row); echo "Alt <br>";
        }
        $this->AccountsNextDt($Dto);

    }

    public function AccountsCancel(AccountsDto $Dto){    //修正データをもとに戻す

        $Dto->AcctAltTbl = $Dto->Accounts;

    }

    private function AccountsDtChk(AccountsDto $Dto){    //すでに修正データがある場合、編集データにコピー

        if($_SESSION['Accounts'] ?? ""){    
            $Dto->AcctAltTbl = $_SESSION['Accounts'];
            unset($_SESSION['Accounts']);
        }  

    }

    private function AccountsNextDt(AccountsDto $Dto){    //次セッション、renderデータ準備

        $Dto->AcctAltTbl = array_values($Dto->AcctAltTbl); 
        //$Dto->Accounts = array_values($Dto->AcctAltTbl); 
        $_SESSION['Accounts']   = $Dto->AcctAltTbl;
 
    }

}