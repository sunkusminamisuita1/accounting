<?php
require_once ROOT_PATH . '/app/dto/shopsDto.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/validators/shopsValidator.php';

class shopController{

	Public        $service;
    public        $dto;
    public        $ctrerrMsgPopUp;
    private       $newShopRegisterBkup;
    private       $pdo;

	public function __construct($pdo)
    {
        $this->pdo      = $pdo;
        $debugMode = 'false';
        $this->dto   			=   new shopsDto();
		$this->dto->user		=	$_SESSION['user']??"";

		$this->dto->shopAltTbl	= 	empty($_SESSION['shopAltTbl'])
                                    ? $_SESSION['userShops']
                                    : $_SESSION['shopAltTbl'] ;
        $_SESSION['shopAltTbl'] =   $this->dto->shopAltTbl;
		$this->ctrerrMsgPopUp   =   new errMsgPopUp($this->dto);
        $this->service   		=   new shopsService($this->dto,$this->pdo);
        $this->newShopRegisterBkup = [];        
    }

    public function switch() //procslct.phpから呼ばれる
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo "<pre>";
		    var_dump($_SESSION['sisanStartEnd']??[]);
			$targetShopId = $_POST['active_shop'] ?? '';
			// 所有している店舗リストの中に、選択されたIDが存在するか安全チェック
			$validShop = false;
			if ($targetShopId === '   all') {
				$validShop = true;
				$_SESSION['currentShopCode'] = '   all';
				$_SESSION['current_shop_name'] = '全店合算';
			} else {

				foreach ($_SESSION['shopAltTbl'] as $i=>$shop) {

					if ((string)$shop['shop_code'] === $targetShopId) {
						$_SESSION['currentShopCode'] = (string)$shop['shop_code'];
                        //echo "<br>shopController.switch shop_code: " . var_dump($_SESSION['currentShopCode']) . "<br>";
						$_SESSION['current_shop_name'] = (string)$shop['shop_name'];
						$validShop = true;
						break;
					}
				}
				if(!$validShop){
					echo "<br>err shopcontoroller.switch 入力shop_idがありません";exit;
				}
                $_SESSION['activeShopCode'] = (string)$shop['shop_code'];

			}
			// 元のページ（またはホーム）に戻す
			$returnRoute = $_SESSION['current_route'] ?? 'home';
			header("Location: index.php?route={$returnRoute}");
			exit;
		}
	}

	//shopデータ登録、更新、削除
    public function edit()
    {
        $this->dto->user = $_SESSION['user'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		    requireCsrf();

            $this->dto->postDt      =   $_POST ?? [];
            $this->dto->sessionDt   =   $_SESSION ?? [];
            $this->service->restoreEditingData();

            $_SESSION['isValidated']    =   '';

            switch($_POST['shopsPfm']){

                case '登録実行': //新規登録データを編集エリアに追加する
                    $this->service->tourokuJikkou();
                    break;

                case '修正実行':  //shopAltTblの内容をDBに反映する。
                    $this->service->syuuseiJikkou();  
                    break;

                case 'キャンセル':
                    $this->service->cancel();
                    break;
                
                case '削除店舗復元':
                    $this->service->sakujyoTenpoFukugen();
                    break;
            }
            $this->prepareNextRequest();
            
        }
        $this->render();
    }
//
    private function render(){

        //CSRFトークンを生成してセッションに保存
        $tokenKey = generateCsrfToken();

        //修正実行時のrender

        if(empty($this->dto->shopAltTbl??'[]')){
            $ShopList   =   $this->service->getShopsData();
        }else{
            $ShopList   =   $this->dto->shopAltTbl??'[]';
        }
        //echo "<pre>";
        //var_dump($ShopList);
        //echo "</pre>";
        $ShopList = array_filter($ShopList,fn($row) =>
                $row['deleted'] !== '1'
            ||  $_POST['shopsPfm'] === '削除店舗復元'
        );

        //新規登録のrender
        if($_POST['shopsPfm'] ?? '' === '登録実行'){
            
            $newShopsCode   =   $this->dto->newShopCode ?? '';
            $newShopName    =   $this->dto->newShopName ?? '';
            $newOpenDate    =   $this->dto->newOpenDate ?? '';
            $newSummary     =   $this->dto->newSummary ?? '';
            $newErrMsg      =   $this->dto->newErrMsg ?? '';

        }
        require ROOT_PATH.'/views/shopsView.php';
        $this->dto->shopAltTbl = [];
        $_SESSION['shopAltTbl'] = $ShopList;
    }

    private function prepareNextRequest(){    //次セッション、renderデータ準備
        $_SESSION['shopAltTbl']   = $this->dto->shopAltTbl;
    }
}	