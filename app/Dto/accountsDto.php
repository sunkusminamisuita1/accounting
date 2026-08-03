<?php
// app/Dto/accountsDto.php
class accountsDto{
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
    public array $accounts = [];
    public string $password;
    public array $acctAltTbl = [];
    public array $ErrData = []; //エラー行の配列 ['ModName' => 'エラーメッセージ']
    public array $accountsType = [];
    public array $EditedRow = [];
    public array $PostDt = [];


    public function __construct()    {
        $this->id          =   $_SESSION['user']['id']??'0';  //UserId
        $this->username    =   $_SESSION['user']['username']??'';  //UserId
        $this->email       =   $_SESSION['user']['email']??'';  //UserId
        $this->accounts    =   [];
        $this->accountsType =  ['収益',
                                '費用',
                                '資産',
                                '負債',
                                '資本'
                                ];

    }

}
?>