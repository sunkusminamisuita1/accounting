<?php
//　repositoryは Userrepository.phpを使用する。

require_once ROOT_PATH.'/app/repositories/UserRepository.php';
require_once ROOT_PATH.'/app/repositories/ShopsRepository.php';

class shopsService{
    public      $ctrErrMsgPopUp;
    public      $Repo;
    public      $SvcVali;

	public function __construct()
    {
        $this->Repo = new ShopsRepository();
        $this->SvcVali = new ShopsValidator();
    }

    public function RenewTargetShopCode( $Dto): array
    {

        $Dto->GetShopCode   =   isset($_POST['active_shop']) ? $_POST['active_shop'] : '     1';

        $Dto->ShopAltTbl    =   empty($Dto->ShopAltTbl) 
                                    ? $_SESSION['ShopAltTbl'] 
                                    : $Dto->ShopAltTbl ;

        foreach($Dto->ShopAltTbl as $Key => $Row   )
        {
                $test1 = (int)trim($Dto->GetShopCode);
                $test2 = (int)trim($Row['shop_code']);

            if( $test1 === $test2 ?? 1)
            {
                $Dto->TargetShop     =   $Row;
                return $Row;
            }
        }
        echo "エラー shopsService(RenewTargetShopCode) 入力された店名がありません。";
        exit;

    }




    public function getShopsData( $Dto): array
    {
        //呼び出し元　使用方法　http://test5.local/index.php?route= h($RtnRoute) 
        $RtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
        $RtnRoute = ltrim(strchr($RtnRoute,'route='), 'route='); //'='

        $Dto->UserShops         =   $this->Repo->getShopsByUserId($Dto);
        

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

        return $Dto->UserShops;
    }

    public function ShopsAdd(ShopsDto $Dto){

        $UserId = $Dto->User['id'];

        array_unshift($Dto->ShopAltTbl,['id'        =>  null,                           'user_id'       =>  (int)$UserId, 
                                        'shop_code' =>  $_POST['NewShopCode'],          'shop_name'     =>  $_POST['NewShopName'],
                                        'open_date' =>  $_POST['NewOpenDate'],          'adress'        =>  '',
                                        'closed'    =>  0,                              'closed_date'   =>  '', 
                                        'summary'   =>  $_POST['NewSummary'],           'edittype'      =>'追加'
                                        ]                                       
        );
    }

    public function LineDlt(ShopsDto $Dto){
        
        foreach($Dto->PostDt['ShopsUpdDt'] as $Key => $Row)
        {
            echo "<br>llllll= {$Dto->PostDt['ShopsUpdDt'][$Key]['deletekey']}";
            $DltKey     =   ! empty($Row['deletekey'])
                            ? $Row['deletekey']
                            : "";
            if(!empty($DltKey))
            {
                 break; 
            }
            
        }

        $this->RepoDataMake($Dto);
        array_splice($Dto->ShopAltTbl, $DltKey, 1);
        $_SESSION['ShopAltTbl'] =   $Dto->ShopAltTbl;

    } 

    public function RepoDataMake(ShopsDto $Dto){
  
            // 検索を高速化するため、セッションの店舗一覧を shop_code をキーにした連想配列に変換（準備）
        $existingShops = array_column($_SESSION['UserShops'] ?? [], null, 'shop_code');

        $Dto->ShopAltTbl = []; // 初期化
        //var_dump($Dto->PostDt['ShopsUpdDt']);
        //追加データは既に処理済みのため読み飛ばす
        foreach ($Dto->PostDt['ShopsUpdDt'] as $Key => $Row) {

            // 1. edittype（編集タイプ）の判定
            if (!empty($Row['delete'])) {
                $edittype = '削除';
            } else {
        
                // 既存データ（$_SESSION）の中に同じ shop_code が存在するか？
                if (isset($existingShops[$Row['shop_code'] ?? ''])) {
                    $existingRow = $existingShops[$Row['shop_code'] ?? ''];

                    // 既存データと入力データを比較（変更があるか？）
                    // ※比較用に必要なフィールドだけチェックするか、全フィールドを比較
                    $isChanged = ($Row['shop_name']   !== $existingRow['shop_name'])
                            || ($Row['open_date']     !== $existingRow['open_date'])
                            || ($Row['summary']       !== $existingRow['summary'])
                            || (($Row['closed']?? null )  !== ($existingRow['closed']??''))
                            || ($Row['closed_date']   !== $existingRow['closed_date']);

                    $edittype = $isChanged ? '更新' : '';
                } else {
                    // 既存に存在しないコードなら「追加」
                    $edittype = '追加';
                }
            }

            // 2. 配列にまとめてセット
            $Dto->ShopAltTbl[$Key] = [
                'id'          => null,
                'shop_code'   => $Row['shop_code'] ?? '',
                'shop_name'   => $Row['shop_name'] ?? '',
                'open_date'   => $Row['open_date'] ?? '',
                'summary'     => $Row['summary'] ?? '',
                'closed'      => $Row['closed'] ?? '',
                'closed_date' => $Row['closed_date'] ?? '',
                'edittype'    => $edittype,
            ];
        }
    }

    public function ShopsAlt(ShopsDto $Dto){

        $Err = $this->SvcVali->CommonVali($Dto);
        if($Err > 0){
            return;
        }

        foreach($Dto->ShopAltTbl as $Key=>$Row){
            //var_dump($Row);
            //echo "<br>";
            switch($Row['edittype']){
                case '追加':
                    //var_dump($_SESSION['UserShops']); exit;
                    $this->Repo->ShopsAdd($Dto,$Key);
                    break;
                case '更新':
                    //var_dump($_SESSION['UserShops']); exit;
                    $this->Repo->ShopsAlt($Dto,$Key);
                    break;
                case '削除':
                    //var_dump($_SESSION['UserShops']); exit;
                    $this->Repo->ShopsDlt($Dto,$Key);
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