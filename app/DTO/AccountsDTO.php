<?php
// app/DTO/AccountsDTO.php

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
    public string $Accounts;
    public string $password;
    public AccountsValidator $Validator;
    public VoucherRepository $VoucherRepository;

    public function __construct()    {
        $this->VoucherRepository = new VoucherRepository();
        $this->Validator = new AccountsValidator();
        $this->id          =   $_SESSION['user']['id'];  //UserId
        $this->username    =   $_SESSION['user']['username'];  //UserId
        $this->email       =   $_SESSION['user']['email'];  //UserId
    }

}

?>