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

    public function GetAccounts()
    {
        $this->CtrDto->Accounts  =   $this->SvcRepo->getAccounts();
        foreach($this->CtrDto->Accounts as $key=>$Row){
            //echo "key={$key} = {$Row['id']}{$Row['name']} {$Row['type']}<br>";
        }
        //echo "xxxxx={$this->CtrDto->Accounts[1]['name']} <br>";
        //exit;
    }

    public function AccountsDlt()
    {
        foreach($this->CtrDto->AcctDltTbl as $key=>$Row){
            var_dump($Row); echo "Dlt<br>";
        }
    }

        public function AccountsAdd()
    {
        foreach($this->CtrDto->AcctDltTbl as $key=>$Row){
            var_dump($Row); echo "Add <br>";
        }
    }

        public function AccountsAlt()
    {
        foreach($this->CtrDto->AcctDltTbl as $key=>$Row){
            var_dump($Row); echo "Alt <br>";
        }
    }

}