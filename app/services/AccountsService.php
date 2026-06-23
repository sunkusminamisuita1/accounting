<?php
// app/services/AuthService.php
require_once ROOT_PATH.'/app/repositories/UserRepository.php';
require_once ROOT_PATH.'/app/repositories/voucherRepository.php';
require_once ROOT_PATH.'/app/DTO/AccountsDTO.php';

class AccountsService
{
    //public AccountsRepository   $Repo;
    public AccountsDTO          $Dto;
    public VoucherRepository    $VcrRepo;

    public function __construct($Dto)
    {
        //$this->Repo = new AccountsRepository();
        //$this->repo = new AccountsRepository();
        //$this->Dto  = new AccountsDTO($_SESSION['user']['id']);
        $this->Dto  = new AccountsDTO();

    }

    public function GetAccounts($Dto)
    {
        $this->Dto->Accounts  =   $Dto->Repo->getAccounts();

        return $this->Dto->Accounts;
    }
}