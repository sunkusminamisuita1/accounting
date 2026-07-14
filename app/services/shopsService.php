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

    public function RenewTargetShopCode( $Dto): array
    {

        //if(!isset($_POST['active_shop']))
        //{
        //    echo "xxxx";
        //}
        $Dto->GetShopCode   =   isset($_POST['active_shop']) ? $_POST['active_shop'] : 1;

        //var_dump($Dto->UserShops);echo "ddddddddddddddddddd";exit;

        foreach($Dto->UserShops as $Key => $Row   )
        {
            //echo "<br>getshopcode=" . (int)$Dto->GetShopCode . "<br>row['shop_code=']" . (int)$Row['shop_code'];
            if((int)$Dto->GetShopCode === (int)$Row['shop_code']??"")
            {
                $Dto->TargetShop     =   $Row;
                return $Row;
            }
        }
        echo "エラー shopsService.php 入力された店名がありません。";
        exit;

    }




    public function getShopsData( $Dto): array
    {
        //呼び出し元　使用方法　http://test5.local/index.php?route= h($RtnRoute) 
        $RtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
        $RtnRoute = ltrim(strchr($RtnRoute,'route='), 'route='); //'='

        $UserShops         =   $this->Repo->getShopsByUserId($Dto);
        

        $Dto->ShopAltTbl             =   $Dto->UserShops; //Shop修正用テーブル作成
        // 初期選択店舗として、リストの先頭にある店舗のIDを「現在の操作店舗」としてセット
        if (!empty($Dto->UserShops)??"") {
            $_SESSION['current_shop_code'] = $Dto->UserShops[0]['shop_code']; 
            $_SESSION['current_shop_name'] = $Dto->UserShops[0]['shop_name'];
        } else {
        // 店舗が未登録の場合のフォールバック
            $_SESSION['current_shop_code'] = 0;
            $_SESSION['current_shop_name'] = "店舗未登録";
        }
        //var_dump($_SESSION['current_shop_code']);echo "ddddddddddddddddddd";exit;

        return $UserShops;
    }

    public function ShopsAdd(ShopsDto $Dto){

        $UserId = $Dto->User['id'];
        //echo "ShopAdd method";exit;
        //| id | user_id | shop_code | shop_name    | open_date | address | closed | closed_date | summary | created_at          |

        array_unshift($Dto->ShopAltTbl,['id'        =>  null,       'user_id'       =>  (int)$UserId, 
                                        'shop_code' =>  '',         'shop_name'     =>  '',
                                        'open_date' =>  '',         'adress'        =>  '',
                                        'closed'    =>  0,          'closed_date'   =>  '', 
                                        'summary'   =>  '',         'edittype'=>'追加']);


                                        
    }

        public function RepoDataMake(ShopsDto $Dto){
            echo "<br>repodatamake method".var_dump($Dto->PostDt['ShopsUpdDt']); exit;
            //echo "hhh";exit;
            foreach($Dto->PostDt['ShopsUpdDt'] as $Key=>$Row){ //array_Spliceでキー順序が更新されるため、削除は降順で実行
                $Dto->ShopAltTbl[$Key]['shop_code']     = $Dto->PostDt['ShopsUpdDt'][$Key]['shop_code'];
                $Dto->ShopAltTbl[$Key]['shop_name']     = $Dto->PostDt['ShopsUpdDt'][$Key]['shop_name'];
                $Dto->ShopAltTbl[$Key]['summary']       = $Dto->PostDt['ShopsUpdDt'][$Key]['summary'];
                $Dto->ShopAltTbl[$Key]['closed']        = $Dto->PostDt['ShopsUpdDt'][$Key]['closed'];
                $Dto->ShopAltTbl[$Key]['closed_date']   = $Dto->PostDt['ShopsUpdDt'][$Key]['closed_date'];

            }

    }

    public function ShopsAlt(ShopsDto $Dto){

        $Err = $this->SvcVali->ShopsVali($Dto);
        if($Err > 0){
            return;
        }

        foreach($Dto->ShopsAltTbl as $Key=>$Row){

            switch($Row['edittype']){
                case '追加':
                    $this->SvcRepo->ShopsAdd($Dto,$Key);
                    break;
                case '更新':
                    $this->SvcRepo->ShopsEdit($Dto,$Key);
                    break;
                case '削除':
                    $this->SvcRepo->ShopsDlt($Dto,$Key);
                    break;
                default:
                    echo "system error: edittype is not set.";
                    exit;
                    break;
            }
        }
    }

}
?>