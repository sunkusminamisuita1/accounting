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
    public array $Accounts = [];
    public string $password;
    public array $AcctAltTbl = [];
    public array $ErrData = []; //エラー行の配列 ['ModName' => 'エラーメッセージ']
    public array $AccountsType = [];


    public function __construct()    {
        $this->id          =   $_SESSION['user']['id']??'0';  //UserId
        $this->username    =   $_SESSION['user']['username']??'';  //UserId
        $this->email       =   $_SESSION['user']['email']??'';  //UserId
        $this->Accounts    =   [];
        $this->AccountsType =  ['収益',
                                '費用',
                                '資産',
                                '負債',
                                '資本',
                                '収益'
                                ];

    }

}
?>