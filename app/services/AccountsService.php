<?php
// app/services/AuthService.php
require_once ROOT_PATH.'/app/repositories/UserRepository.php';
require_once ROOT_PATH.'/app/repositories/voucherRepository.php';
require_once ROOT_PATH.'/app/DTO/AccountsDTO.php';

class AccountsService
{
    private $repo;
    private $dto;
    private $VcrRepo;

    public function __construct()
    {
        var_dump($_SESSION['user']);
        $this->VcrRepo = new voucherRepository();
        //$this->repo = new AccountsRepository();
        $this->dto  = new AccountsDTO($_SESSION[''], $_SESSIOM['']);

    }

    public function GetAccounts()
    {
        $this->Dto->Accounts  =   $this->VcrRepo->getAccounts();

        return ;
    }
}