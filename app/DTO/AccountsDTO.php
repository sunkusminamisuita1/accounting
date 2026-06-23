<?php
// app/DTO/AccountsDTO.php
require_once ROOT_PATH . '/app/services/AccountsService.php';
require_once ROOT_PATH . '/app/Validators/AccountsValidator.php';
require_once ROOT_PATH . '/app/repositories/AccountsRepository.php';

class AccountsDTO{
//                $_SESSION['user'] = [
//                    'id' => (int)$user['id'],
//                    'username' => $user['username'],
//                    'email' => $user['email'],
//                    'fiscalMonth' => $user['fiscal_month'],
//                    'fiscalDay' => $user['fiscal_day']
//                ];
    public int $id;
    public string $username;
    public string $email;
    public array $Accounts;
    public string $password;
    public AccountsDTO          $Dto;
    public AccountsService      $Service;
    public AccountsValidator    $Validator;
    public AccountsRepository   $Repository;
    public VoucherRepository    $VoucherRepository;


    public function __construct()    {
        $this->id          =   $_SESSION['user']['id']??'0';  //UserId
        $this->username    =   $_SESSION['user']['username']??'';  //UserId
        $this->email       =   $_SESSION['user']['email']??'';  //UserId
    }

    public function InstnsSet($Dto)    {
        $this->Dto                  = $Dto;
        $this->Service              = new AccountsService($Dto);        
        $this->Validator            = new AccountsValidator($Dto);
        $this->Repo                 = new AccountsRepository($Dto);
        $this->VoucherRepository    = new VoucherRepository($Dto);
    }
}
?>