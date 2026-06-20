<?php
// app/services/AuthService.php
require_once ROOT_PATH.'/app/repositories/UserRepository.php';

class AccountsService
{
    private $repo;
    private $dto;

    public function __construct()
    {
        $this->repo = new AccountsRepository();
        $this->dto  = new AccountsDTO();

    }

    public function GetAccounts()
    {
        $VoucherRepo = new VoucherRepository();
        $this->Dto->Accounts  =   $VoucherRepo->getAccounts();

        return ;
    }
}