<?php
// app/dto/accountsDto.php
class accountsDto{
//                $_SESSION['user'] = [
//                    'id' => (int)$user['id'],
//                    'username' => $user['username'],
//                    'email' => $user['email'],
//                    'fiscalMonth' => $user['fiscal_month'],
//                    'fiscalDay' => $user['fiscal_day']
//                ];
    public int $id;
    public string $userName;
    public string $email;
    public array $accounts = [];
    public string $password;
    public array $acctAltTbl = [];
    public array $errData = []; //エラー行の配列 ['ModName' => 'エラーメッセージ']
    public array $accountsType = [];
    public array $editedRow = [];
    public array $postDt = [];


    public function __construct()    {
        $this->id          =   $_SESSION['user']['id']??'0';  //UserId
        $this->userName    =   $_SESSION['user']['userName']??'';  //UserId
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