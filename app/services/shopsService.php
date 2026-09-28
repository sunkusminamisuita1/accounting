<?php
//　repositoryは Userrepository.phpを使用する。

require_once ROOT_PATH.'/app/repositories/userRepository.php';
require_once ROOT_PATH.'/app/repositories/shopsRepository.php';
require_once ROOT_PATH.'/app/dto/shopsDto.php';


class shopsService{
    public      $ctrerrMsgPopUp;
    public      $repo;
    public      $vali;
    private     $dto;
    private     $pdo;
    public      $newShopRegisterBkup = [];

	public function __construct(shopsDto $dto,  $pdo)
    {
        $this->repo     = new shopsRepository($dto, $pdo);
        $this->vali     = new shopsValidator($dto,$pdo,false);
        $this->dto      =   $dto;
        $this->pdo      =   $pdo;
    }

    public function tourokuJikkou(){
        $this->newShopRegisterBkup = $this->dto->shopAltTbl;
        $iserror = $this->vali->newRegister();
        if( ! $iserror){
            $this->shopsAdd();
        }
    }

    public function syuuseiJikkou(){
        $this->repoDataMake();
        $isError                  =   $this->vali->commonVali();
        $viewEditKey              =   $_POST['viewEditKey'] ?? null; //修正表　行インデックス
        $_SESSION['shopAltTbl']   =    $this->dto->shopAltTbl;
        if(!$isError){
            $this->shopsAlt($viewEditKey);
            $_SESSION['shopAltTbl']   =   [];
        }
    }

    public function cancel(){
        $this->restoreEditingData();
    }


    public function sakujyoTenpoFukugen(){
        $this->dto->shopAltTbl =   [];
        $this->dto->shopAltTbl =   $this->getAllShopsData();
    }

    public function restoreEditingData(){    //すでに修正データがある場合、編集データにコピー
        $this->dto->shopAltTbl = !empty($_SESSION['shopAltTbl']) 
                            ? $_SESSION['shopAltTbl']                   //前トランの変更データがある時
                            : $this->dto->userShops;                          //変更データが存在しない時、初期読み込みデータを代入
        $_SESSION['shopAltTbl'] =   $this->dto->shopAltTbl;
    }

    public function repoDataMake(){
  
        //     // 検索を高速化するため、セッションの店舗一覧を shop_code をキーにした連想配列に変換（準備）
        $allShops = $this->getAllShopsData();
        $sessionShopsArray = array_column($allShops ?? [], null, 'shop_code');
        $this->dto->shopAltTbl = []; // 初期化
        
        foreach ($this->dto->postDt['shopsUpdDt'] as $pKey => $pRow) {
            $postShopCode    = sprintf('%06d',(int)trim($pRow['shop_code']??0));
            $postShopNme     = trim($pRow['shop_name']??'');
            $postOpenDate    = $this->formatDate( $pRow['open_date']??'');
            $postSummary     = trim((string)$pRow['summary']??'');
            $postClosed      = isset($pRow['closed']) ? trim((string)$pRow['closed']) : '0';
            $postClosedDate  = $this->formatDate($pRow['closed_date']??'');
            $postDelete      = isset($pRow['deleted']) ? trim((string)$pRow['deleted']) : '0';
            $editType = '';
            if(isset($sessionShopsArray[$postShopCode])){
                $sRow   =   $sessionShopsArray[$postShopCode];
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
                }
                $this->P2R( $pKey, $pRow, $editType);
            }else{
                $editType = '追加';
                $this->P2R( $pKey, $pRow, $editType);

            }
        }
    }

    private function P2R($pKey, $pRow, $editType){

            $this->dto->shopAltTbl[$pKey] = [
                'id'          => null,
                'shop_code'   => $pRow['shop_code'] ?? '',
                'shop_name'   => $pRow['shop_name'] ?? '',
                'open_date'   => $this->formatdate($pRow['open_date'] ?? ''),
                'summary'     => $pRow['summary'] ?? '',
                'closed'      => $pRow['closed'] ?? '',
                'closed_date' => $this->formatdate($pRow['closed_date'] ?? ''),
                'editType'    => $editType,
                'deleted'     => isset($pRow['deleted']) ? $pRow['deleted'] : '0'
            ];
    }

    public function renewTargetShopCode(): array{

        $this->dto->getShopCode   =   isset($_POST['active_shop']) ? $_POST['active_shop'] : '     1';

        $this->dto->shopAltTbl    =   empty($this->dto->shopAltTbl) 
                                    ? $_SESSION['shopAltTbl'] 
                                    : $this->dto->shopAltTbl ;

        foreach($this->dto->shopAltTbl as $key => $row   )
        {
                $test1 = (int)trim($this->dto->getShopCode);
                $test2 = (int)trim($row['shop_code']);


            if( $test1 === $test2 ?? 1 )
            {
                $this->dto->targetShop     =   $row;
                return $row;
            }
        }
    }

    public function getShopsData(): array{
        //呼び出し元　使用方法　http://test5.local/index.php?route= h($rtnRoute) 
        $rtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
        $rtnRoute = ltrim(strchr($rtnRoute,'route='), 'route='); //'='
        $this->dto->userShops         =   $this->repo->getShopsByUserId( (int)0);
        $this->dto->shopAltTbl             =   $this->dto->userShops; //Shop修正用テーブル作成
        // 初期選択店舗として、リストの先頭にある店舗のIDを「現在の操作店舗」としてセット
        if (!empty($this->dto->userShops)??"") {
            $_SESSION['currentShopCode'] = $this->dto->userShops[0]['shop_code']; 
            $_SESSION['current_shop_name'] = $this->dto->userShops[0]['shop_name'];
        } else {
        // 店舗が未登録の場合のフォールバック
            $_SESSION['currentShopCode'] = 0;
            $_SESSION['current_shop_name'] = "店舗未登録";
        }
        return $this->dto->userShops;
    }

    public function getAllShopsData(): array{
        $this->dto->shopAltTbl = [];
        $curShopList = $this->repo->getShopsByUserId((int)0);
        $delShopList = $this->repo->getShopsByUserId((int)1);
        $allShopList = array_merge($curShopList, $delShopList);

        usort($allShopList, function (array $a, array $b): int {
            return (int)($a['shop_code'] ?? 0)
                <=> (int)($b['shop_code'] ?? 0);
        });


        $this->dto->shopAltTbl = $allShopList; //Shop修正用テーブル作成
        return $allShopList;
    }

    public function shopsAdd(){

        $userId = $this->dto->user['id'];

        array_unshift($this->dto->shopAltTbl,['id'        =>  null,                         'user_id'     =>  (int)$userId ?? 0, 
                                        'shop_code' =>  $_POST['newShopCode'] ?? '',  'shop_name'   =>  $_POST['newShopName'] ?? '',
                                        'open_date' =>  $_POST['newOpenDate'] ?? '',  'adress'      =>  '',
                                        'closed'    =>  0,                            'closed_date' =>  '', 
                                        'summary'   =>  $_POST['newSummary'] ?? '',   'editType'    =>'追加',
                                        'deleted'   =>  '0'
                                        ]                                       
        );
    }

    public function LineDlt(){
        
        foreach($this->dto->postDt['shopsUpdDt'] as $key => $row)
        {
            $dltKey     =   ! empty($row['deletekey'])
                            ? $row['deletekey']
                            : "";
            if(!empty($dltKey))
            {
                echo "shopService.LineDlt プログラムエラー　行削除で行番号が指定されていません！";
                 break; 
            }   
        }
    } 

    private function formatDate(string $date): string {
        $date   =   trim($date);
        if ($date !== '' && strlen($date) === 8 && is_numeric($date)) {
            return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        }
        return $date;
    }   

    public function shopsAlt(){

        $err = $this->vali->commonVali();
        if($err > 0){
            return $err;
        }

        foreach($this->dto->shopAltTbl as $key=>$row){
            switch($row['editType']){
                case '追加':
                    $this->repo->shopsAdd($key);
                    break;
                case '更新':
                    $this->repo->shopsAlt($key);
                    break;

                case '': // 変更なし
                    break;
                default:
                    break;
            }
        }
    }

}
?>