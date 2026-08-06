<?php
//　repositoryは Userrepository.phpを使用する。

require_once ROOT_PATH.'/app/repositories/userRepository.php';
require_once ROOT_PATH.'/app/repositories/shopsRepository.php';

class shopsService{
    public      $ctrerrMsgPopUp;
    public      $repo;
    public      $SvcVali;

	public function __construct()
    {
        $this->repo = new shopsRepository();
        $this->SvcVali = new shopsValidator();
    }

    public function renewTargetShopCode( $dto): array
    {

        $dto->getShopCode   =   isset($_POST['active_shop']) ? $_POST['active_shop'] : '     1';

        $dto->shopAltTbl    =   empty($dto->shopAltTbl) 
                                    ? $_SESSION['shopAltTbl'] 
                                    : $dto->shopAltTbl ;

        foreach($dto->shopAltTbl as $Key => $row   )
        {
                $test1 = (int)trim($dto->getShopCode);
                $test2 = (int)trim($row['shop_code']);

            if( $test1 === $test2 ?? 1)
            {
                $dto->TargetShop     =   $row;
                return $row;
            }
        }
        echo "エラー shopsService(renewTargetShopCode) 入力された店名がありません。";
        exit;

    }




    public function getShopsData( $dto): array
    {
        //呼び出し元　使用方法　http://test5.local/index.php?route= h($rtnRoute) 
        $rtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
        $rtnRoute = ltrim(strchr($rtnRoute,'route='), 'route='); //'='

        $dto->userShops         =   $this->repo->getShopsByUserId($dto);
        

        $dto->shopAltTbl             =   $dto->userShops; //Shop修正用テーブル作成
        // 初期選択店舗として、リストの先頭にある店舗のIDを「現在の操作店舗」としてセット
        if (!empty($dto->userShops)??"") {
            $_SESSION['current_shop_code'] = $dto->userShops[0]['shop_code']; 
            $_SESSION['current_shop_name'] = $dto->userShops[0]['shop_name'];
        } else {
        // 店舗が未登録の場合のフォールバック
            $_SESSION['current_shop_code'] = 0;
            $_SESSION['current_shop_name'] = "店舗未登録";
        }

        return $dto->userShops;
    }

    public function shopsAdd(shopsDto $dto){

        $userId = $dto->user['id'];

        array_unshift($dto->shopAltTbl,['id'        =>  null,                           'user_id'       =>  (int)$userId, 
                                        'shop_code' =>  $_POST['NewShopCode'],          'shop_name'     =>  $_POST['NewShopName'],
                                        'open_date' =>  $_POST['NewOpenDate'],          'adress'        =>  '',
                                        'closed'    =>  0,                              'closed_date'   =>  '', 
                                        'summary'   =>  $_POST['NewSummary'],           'EditType'      =>'追加'
                                        ]                                       
        );
    }

    public function LineDlt(shopsDto $dto){

        //$dto->isLocked  =   "readonly";
        
        foreach($dto->postDt['shopsUpdDt'] as $Key => $row)
        {
            //echo "<br>llllll= {$dto->postDt['shopsUpdDt'][$Key]['deletekey']}";
            $dltKey     =   ! empty($row['deletekey'])
                            ? $row['deletekey']
                            : "";
            if(!empty($dltKey))
            {
                echo "shopService.LineDlt プログラムエラー　行削除で行番号が指定されていません！";
                 break; 
            }
            
        }

        //$this->repoDataMake($dto);
        //array_splice($dto->shopAltTbl, $dltKey, 1);
        //$_SESSION['shopAltTbl'] =   $dto->shopAltTbl;

    } 

    public function repoDataMake(shopsDto $dto){
  
        //     // 検索を高速化するため、セッションの店舗一覧を shop_code をキーにした連想配列に変換（準備）
        $sessionShopsArray = array_column($_SESSION['userShops'] ?? [], null, 'shop_code');
        //var_dump($sessionShopsArray);
        //var_dump($sessionShopsArray);exit;
        $dto->shopAltTbl = []; // 初期化
        foreach ($dto->postDt['shopsUpdDt'] as $pKey => $pRow) {
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
                    //$this->P2R($dto, $pKey, $pRow, $editType);
                }

                // if (!empty($pRow['deleted'])) {
                //     $editType = '削除';
                // }

            }else{
                $editType = '追加';
                //$this->P2R($dto, $pKey, $pRow, $editType);
            }
            $this->P2R($dto, $pKey, $pRow, $editType);
                // echo "<br> postShopNme={$postShopNme}  sessionShopNme={$sessionShopNme}";
                // echo "<br> postOpenDate={$postOpenDate}  sessionOpenDate={$sessionOpenDate}";
                // echo "<br> postSummary={$postSummary}  sessionSummary={$sessionSummary}";
                // echo "<br> postClosed={$postClosed}  sessionClosed={$sessionClosed}";
                // echo "<br> postClosedDate={$postClosedDate}  sessionClosedDate={$sessionClosedDate}";
                // echo "<br> postDelete={$postDelete}  sessionDelete={$sessionDelete}";
                // echo "<br> isChanged={$isChanged}";
        }
        //exit; //デバッグ
        //var_dump($dto->postDt['shopsUpdDt']);exit;  

    }

    private function P2R($dto, $pKey, $pRow, $editType){

            // 2. 配列にまとめてセット
            $dto->shopAltTbl[$pKey] = [
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
            //var_dump($dto->shopAltTbl[$pKey]);
    }

    private function formatDate(string $date): string {
        $date   =   trim($date);
        if ($date !== '' && strlen($date) === 8 && is_numeric($date)) {
            return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        }
        return $date;
    }

    

    public function shopsAlt(shopsDto $dto){

        $err = $this->SvcVali->commonVali($dto);
        if($err > 0){
            return;
        }

        foreach($dto->shopAltTbl as $Key=>$row){
            //var_dump($row);
            //echo "<br>";
            switch($row['edittype']){
                case '追加':
                    //var_dump($_SESSION['userShops']); exit;
                    $this->repo->shopsAdd($dto,$Key);
                    break;
                case '更新':
                    //var_dump($_SESSION['userShops']); exit;
                    $this->repo->shopsAlt($dto,$Key);
                    break;
                // case '削除':
                //     //var_dump($_SESSION['userShops']); exit;
                //     $this->repo->ShopsDlt($dto,$Key);
                //     break;
                case '': // 変更なし
                    //var_dump($_SESSION['userShops']); exit;
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