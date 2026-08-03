<?php
//　repositoryは Userrepository.phpを使用する。

require_once ROOT_PATH.'/app/repositories/userRepository.php';
require_once ROOT_PATH.'/app/repositories/shopsRepository.php';

class shopsService{
    public      $ctrerrMsgPopUp;
    public      $Repo;
    public      $SvcVali;

	public function __construct()
    {
        $this->Repo = new shopsRepository();
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
                                        'summary'   =>  $_POST['NewSummary'],           'EditType'      =>'追加'
                                        ]                                       
        );
    }

    public function LineDlt(ShopsDto $Dto){

        //$Dto->isLocked  =   "readonly";
        
        foreach($Dto->PostDt['ShopsUpdDt'] as $Key => $Row)
        {
            //echo "<br>llllll= {$Dto->PostDt['ShopsUpdDt'][$Key]['deletekey']}";
            $DltKey     =   ! empty($Row['deletekey'])
                            ? $Row['deletekey']
                            : "";
            if(!empty($DltKey))
            {
                echo "shopService.LineDlt プログラムエラー　行削除で行番号が指定されていません！";
                 break; 
            }
            
        }

        //$this->RepoDataMake($Dto);
        //array_splice($Dto->ShopAltTbl, $DltKey, 1);
        //$_SESSION['ShopAltTbl'] =   $Dto->ShopAltTbl;

    } 

    public function RepoDataMake(ShopsDto $Dto){
  
        //     // 検索を高速化するため、セッションの店舗一覧を shop_code をキーにした連想配列に変換（準備）
        $sessionShopsArray = array_column($_SESSION['UserShops'] ?? [], null, 'shop_code');
        //var_dump($sessionShopsArray);
        //var_dump($sessionShopsArray);exit;
        $Dto->ShopAltTbl = []; // 初期化
        foreach ($Dto->PostDt['ShopsUpdDt'] as $pKey => $pRow) {
            $postShopCode    = sprintf('%06d',(int)trim($pRow['shop_code']??0));
            $postShopNme     = trim($pRow['shop_name']??'');
            $postOpenDate    = $this->formatDate( $pRow['open_date']??'');
            $postSummary     = trim((string)$pRow['summary']??'');
            $postClosed      = isset($pRow['closed']) ? trim((string)$pRow['closed']) : '0';
            $postClosedDate  = $this->formatDate($pRow['closed_date']??'');
            $postDelete      = isset($pRow['deleted']) ? trim((string)$pRow['deleted']) : '0';
            // echo "<br>";
            // print_r($pRow['delete']);
            // echo "<br>";
            // var_dump($sessionShopsArray[$postShopCode]);
            // echo "<br>";
            // var_dump($sessionShopsArray);
            // echo "<br>";exit;
            $editType = '';
            if(isset($sessionShopsArray[$postShopCode])){
                $sRow   =   $sessionShopsArray[$postShopCode];
                //var_dump($sRow);exit;
                $sessionShopCode    = (int)trim($sRow['shop_code']??0);
                $sessionShopNme     = trim($sRow['shop_name']);
                $sessionOpenDate    = $this->formatDate($sRow['open_date']??'');            
                $sessionSummary     = trim((string)$sRow['summary']??'');
                $sessionClosed      = isset($sRow['closed']) ? trim((string)$sRow['closed']) : '0';
                $sessionClosedDate  = $this->formatdate($sRow['closed_date']??'');            
                $sessionDelete      = isset($sRow['deleted']) ? trim((string)$sRow['deleted']) : '0';

                $isChanged =   (
                                $postShopNme       !==  $sessionShopNme       ||
                                $postOpenDate      !==  $sessionOpenDate      ||
                                $postSummary       !==  $sessionSummary       ||
                                $postClosed        !==  $sessionClosed        ||
                                $postClosedDate    !==  $sessionClosedDate    ||
                                $postDelete        !==  $sessionDelete
                               );


                if($isChanged){
                    $editType = $isChanged ? '更新' : '';
                    //$this->P2R($Dto, $pKey, $pRow, $editType);
                }

                // if (!empty($pRow['deleted'])) {
                //     $editType = '削除';
                // }

            }else{
                $editType = '追加';
                //$this->P2R($Dto, $pKey, $pRow, $editType);
            }
            $this->P2R($Dto, $pKey, $pRow, $editType);
                // echo "<br> postShopNme={$postShopNme}  sessionShopNme={$sessionShopNme}";
                // echo "<br> postOpenDate={$postOpenDate}  sessionOpenDate={$sessionOpenDate}";
                // echo "<br> postSummary={$postSummary}  sessionSummary={$sessionSummary}";
                // echo "<br> postClosed={$postClosed}  sessionClosed={$sessionClosed}";
                // echo "<br> postClosedDate={$postClosedDate}  sessionClosedDate={$sessionClosedDate}";
                // echo "<br> postDelete={$postDelete}  sessionDelete={$sessionDelete}";
                // echo "<br> isChanged={$isChanged}";
        }
        //exit; //デバッグ
        //var_dump($Dto->PostDt['ShopsUpdDt']);exit;  

    }

    private function P2R($Dto, $pKey, $pRow, $editType){

            // 2. 配列にまとめてセット
            $Dto->ShopAltTbl[$pKey] = [
                'id'          => null,
                'shop_code'   => $pRow['shop_code'] ?? '',
                'shop_name'   => $pRow['shop_name'] ?? '',
                'open_date'   => $this->formatdate($pRow['open_date'] ?? ''),
                'summary'     => $pRow['summary'] ?? '',
                'closed'      => $pRow['closed'] ?? '',
                'closed_date' => $this->formatdate($pRow['closed_date'] ?? ''),
                'edittype'    => $editType,
                'deleted'     => isset($pRow['deleted']) ? $pRow['deleted'] : '0'
            ];
            //echo "<br><br>";
            //var_dump($Dto->ShopAltTbl[$pKey]);
    }

    private function formatDate(string $date): string {
        $date   =   trim($date);
        if ($date !== '' && strlen($date) === 8 && is_numeric($date)) {
            return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        }
        return $date;
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
                // case '削除':
                //     //var_dump($_SESSION['UserShops']); exit;
                //     $this->Repo->ShopsDlt($Dto,$Key);
                //     break;
                case '': // 変更なし
                    //var_dump($_SESSION['UserShops']); exit;
                    break;
                default:
                    echo "system error: EditType is not set.";
                    exit;
                    break;
            }
        }
    }

}
?>