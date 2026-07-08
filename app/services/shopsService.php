<?php
//　repositoryは Userrepository.phpを使用する。

require_once ROOT_PATH.'/app/repositories/UserRepository.php';

class shopsService{
    public        $ctrErrMsgPopUp;
    public  $repo;

	public function __construct()
    {
        $this->repo = new UserRepository();
    }

    public function getShopsData( $dto): array
    {
        //呼び出し元　使用方法　http://test5.local/index.php?route= h($RtnRoute) 
        $RtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
        $RtnRoute = ltrim(strchr($RtnRoute,'route='), 'route='); //'='

        $dto->shopList = $this->repo->getShopsByUserId($dto);
        $_SESSION['shoplist'] = $dto->shopList;
        // 初期選択店舗として、リストの先頭にある店舗のIDを「現在の操作店舗」としてセット
        if (!empty($dto->shopList)) {
            $_SESSION['current_shop_id'] = $dto->shopList[0]['id']; 
            $_SESSION['current_shop_name'] = $dto->shopList[0]['shop_name'];
        } else {
        // 店舗が未登録の場合のフォールバック
            $_SESSION['current_shop_id'] = 0;
            $_SESSION['current_shop_name'] = "店舗未登録";
        }
        return $dto->shopList;
    }

}
?>