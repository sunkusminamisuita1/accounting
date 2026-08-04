<?php
// app/dto/loginDto.php
class loginDto
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
        $this->user = [];  //SELECT  id, username, email, password_hash,
                            //    fiscal_month, 
                            //    fiscal_day,
                            // WHERE email = ?
        $this->userShops = [];       //SELECT id, shop_code, shop_name
                                    // FROM shops WHERE user_id = ?

    }
}
?>