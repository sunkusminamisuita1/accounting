<?php
// app/DTO/AccountsDto.php
class AccountsDto{
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
    public array $AcctDltTbl = [];


    public function __construct()    {
        $this->id          =   $_SESSION['user']['id']??'0';  //UserId
        $this->username    =   $_SESSION['user']['username']??'';  //UserId
        $this->email       =   $_SESSION['user']['email']??'';  //UserId
        $this->Accounts    =   [];

    }

}
?>