<?php
// app/DTO/ShopsDTO.php
class ShopsDTO
{
    public string   $Email;
    public string   $Password;
    public array    $User;
    public array    $ShopList;
    public int      $GetShopCode; //$_GET['shop_id'] ProcSlict.phpでセットされたものをコントローラーでセットする。
    public array    $TargetShop;  //処理用店情報

    //public function __construct(string $email, string $password)
    public function __construct()
    {
        //$this->email = $email;
        //$this->password = $password;
        $this->Email = "";
        $this->Password = "";
        $this->User = [];  //SELECT  id, username, email, password_hash,
                            //    fiscal_month, 
                            //    fiscal_day,
                            // WHERE email = ?
        $this->ShopList = [];       //SELECT id, shop_code, shop_name
                                    // FROM shops WHERE user_id = ?

    }
}
?>