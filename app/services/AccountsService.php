<?php
// app/services/AuthService.php
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH.'/app/repositories/AccountsRepository.php';
require_once ROOT_PATH.'/app/DTO/AccountsDto.php';
require_once ROOT_PATH.'/app/Validators/AccountsValidator.php';


class AccountsService{

    public AccountsValidator    $SvcVali;
    public AccountsRepository   $SvcRepo;
    //public AccountsDto          $CtrDto;

    public function __construct(AccountsDto $Dto)    {
        $this->SvcRepo =   new AccountsRepository($Dto);
        $this->SvcVali =   new AccountsValidator($Dto);
    }

    public function GetAccounts( AccountsDto $Dto){

        $Dto->Accounts  =   $this->SvcRepo->getAccounts($Dto);
        echo "<br><pre>" . var_dump($Dto->Accounts) . "</pre><br>";

        $Dto->AcctAltTbl = $Dto->Accounts;         //修正用科目テーブル作成

        foreach($Dto->AcctAltTbl as $key=>$Row){   //errmsgカラム追加,初期化
            $Dto->AcctAltTbl[$key]['errmsg'] = 'xxxx';
            $Dto->AcctAltTbl[$key]['edittype'] = '更新';//初期値セット
        }
        unset($Row);
    }

    public function AccountsEdit(AccountsDto $Dto){
        //echo "<br><pre>" .var_dump($Dto->AcctAltTbl) . "</pre>";

        $DelKeys = [];
        foreach( $Dto->PostDt['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
            if($Row['del'] ?? ''){
                $Dto->AcctAltTbl[$Key]['edittype'] = '削除';
                $Dto->AcctAltTbl[$Key]['errmsg'] = '削除済み';
            }else{
                //$Dto->AcctAltTbl[$Key] = $Row;
                $Dto->AcctAltTbl[$Key]['edittype'] = '更新';
            }            
        }
        
    }

    public function AccountsAdd(AccountsDto $Dto){

        $UserId = $Dto->id;
        array_unshift($Dto->AcctAltTbl,['id'=> null,'user_id'=>(int)$UserId,'name'=>'','type'=>'', 'errmsg'=>'', 'edittype'=>'追加']);

    }

    public function RepoDataMake(AccountsDto $Dto){

        foreach($Dto->PostDt['AcctUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
                $Dto->AcctAltTbl[$Key]['id']        = $Dto->PostDt['AcctUpdDt'][$Key]['id'];
                $Dto->AcctAltTbl[$Key]['user_id']   = $Dto->PostDt['AcctUpdDt'][$Key]['user_id'];
                $Dto->AcctAltTbl[$Key]['name']      = $Dto->PostDt['AcctUpdDt'][$Key]['name'];
                $Dto->AcctAltTbl[$Key]['type']      = $Dto->PostDt['AcctUpdDt'][$Key]['type'];
                //$Dto->AcctAltTbl[$Key]['edittype']  = $Dto->PostDt['AcctUpdDt'][$Key]['edittype'] ?? '';
        }

    }

    public function AccountsAlt(AccountsDto $Dto){

        $this->SvcVali->AccountsVali($Dto);

        foreach($Dto->AcctAltTbl as $Key=>$Row){

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

    public function AccountsCancel(AccountsDto $Dto){    //修正データをもとに戻す

        $Dto->AcctAltTbl = $Dto->Accounts;

    }

}