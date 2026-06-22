<?php
// app/DTO/AccountsDTO.php

class AccountsDTO
{
    public string $Accounts;
    public string $password;
    private $VoucherRepository;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}

?>