<?php
// app/dto/shopsDto.php
class shopsDto
{
    public string   $email;
    public string   $password;
    public array    $user;
    public array    $userShops;      //ユーザーが所有する全shopテーブルのレコード
    public string   $getShopCode; //$_GET['shop_id'] ProcSlict.phpでセットされたものをコントローラーでセットする。
    public array    $targetShop;  //処理用店情報
    public array    $errData = []; //エラー行の配列 ['ModName' => 'エラーメッセージ']
    public array    $shopAltTbl =[]; //ショップ　修正用テーブル
    public string   $activeShopCode = "";
    public array    $postDt = [];
    public string   $isLocked; //編集エリアにエラーがなければ　readonly(view編集エリアにセット)エラーがあれば　''をセット修正可能とする。
 

    //public function __construct(string $email, string $password)
    public function __construct()
    {
        //$this->email = $email;
        //$this->password = $password;
        $this->isLocked = "";
        $this->email = "";
        $this->password = "";
        $this->user = [];  //SELECT  id, username, email, password_hash,
                            //    fiscal_month, 
                            //    fiscal_day,
                            // WHERE email = ?
        $this->userShops = [];       //SELECT id, shop_code, shop_name
                                    // FROM shops WHERE user_id = ?

    }
}
?>