<?php
// app/DTO/LoginDTO.php
class LoginDTO
{
    public string $email;
    public string $password;
    public array  $User;
    //public array  $shopList;
    public array  $UserShops;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
        $this->User = [];  //SELECT  id, username, email, password_hash,
                            //    fiscal_month, 
                            //    fiscal_day,
                            // WHERE email = ?
        $this->UserShops = [];       //SELECT id, shop_code, shop_name
                                    // FROM shops WHERE user_id = ?

    }
}
?>