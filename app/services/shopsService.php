<?php
//　repositoryは Userrepository.phpを使用する。

require_once ROOT_PATH.'/app/repositories/UserRepository.php';
require_once ROOT_PATH.'/app/repositories/ShopsRepository.php';

class shopsService{
    public      $ctrErrMsgPopUp;
    public      $Repo;

	public function __construct()
    {
        $this->Repo = new ShopsRepository();
    }

    public function RenewTargetShopCode( $dto): array
    {
        //Dto->GetShopCode = $_GET['shop_id']??"";
        $Dto->GetShopCode = $_POST['active_shop']??"all";

		if($Dto->GetShopCode !== $Dto->ShopList['shop_code']??""){

            foreach($Dto->ShopList['shop_code'] as $Key => $Row   ){
                if($Dto->GetShopCode !== $Dto->ShopList['shop_code']??""){
                    $Dto->TargetShop     =   $Row;
                    return $Row;
                }
            }
            echo "エラー shopsService.php 入力された店名がありません。";
            exit;

			//$Dto->ShopList = $this->Repo->getShopsByUserId($Dto);
		}
    }




    public function getShopsData( $dto): array
    {
        //呼び出し元　使用方法　http://test5.local/index.php?route= h($RtnRoute) 
        $RtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
        $RtnRoute = ltrim(strchr($RtnRoute,'route='), 'route='); //'='

        $dto->shopList = $this->Repo->getShopsByUserId($dto);
        $_SESSION['shoplist'] = $dto->shopList;
        // 初期選択店舗として、リストの先頭にある店舗のIDを「現在の操作店舗」としてセット
        if (!empty($dto->shopList)) {
            $_SESSION['current_shop_code'] = $dto->shopList[0]['shop_code']; 
            $_SESSION['current_shop_name'] = $dto->shopList[0]['shop_name'];
        } else {
        // 店舗が未登録の場合のフォールバック
            $_SESSION['current_shop_code'] = 0;
            $_SESSION['current_shop_name'] = "店舗未登録";
        }
        return $dto->shopList;
    }

}
?>