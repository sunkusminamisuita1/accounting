<?php
require_once ROOT_PATH . '/app/dto/shopsDto.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/validators/shopsValidator.php';

class shopController{

	Public        $service;
    public        $dto;
    public        $ctrerrMsgPopUp;
	public		  $repo;
	//public		  $ctrerrMsgPopUp;
    private       $shopsVali;

	public function __construct()
    {
        $debugMode = 'false';
        $this->dto   			=   new shopsDto();
		$this->dto->user		=	$_SESSION['user']??"";

		$this->dto->shopAltTbl	= 	empty($_SESSION['shopAltTbl'])
                                    ? $_SESSION['userShops']
                                    : $_SESSION['shopAltTbl'] ;
        $_SESSION['shopAltTbl'] =   $this->dto->shopAltTbl;

        $this->service   		=   new shopsService($this->dto);
        $this->ctrerrMsgPopUp 	= 	new errMsgPopUp($this->dto);
		$this->repo				=	new shopsRepository();
		$this->ctrerrMsgPopUp   =   new errMsgPopUp($this->dto);
        $this->shopsVali        =   new shopsValidator('');
		
    }

    public function switch()
	{
echo "<br>shopController.switch start<br>";
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$targetShopId = $_POST['active_shop'] ?? '';
echo "<br>targetShopId: {$targetShopId}"; var_dump($targetShopId); echo "<br>";
			// 所有している店舗リストの中に、選択されたIDが存在するか安全チェック
			$validShop = false;
			if ($targetShopId === '   all') {
				$validShop = true;
				$_SESSION['current_shop_code'] = '   all';
				$_SESSION['current_shop_name'] = '全店合算';
			} else {

				foreach ($_SESSION['shopAltTbl'] as $i=>$shop) {

					if ((string)$shop['shop_code'] === $targetShopId) {
						$_SESSION['current_shop_code'] = (string)$shop['shop_code'];
                        echo "<br>shopController.switch shop_code: " . var_dump($_SESSION['current_shop_code']) . "<br>";
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
            //var_dump($returnRoute);exit;
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

			//procSlct.phpでShopCodeが変更できるため、最新のShopCodeをサービスに設定
			$this->service->renewTargetShopCode($this->dto);

            $this->dto->postDt = $_POST ?? '';

            $viewEditKey = $_POST['viewEditKey'] ?? null; //修正表　行インデックス

            $this->restoreEditingData($this->dto);

            $_SESSION['isValidated']    =   '';

            switch($_POST['shopsPfm']){

                case '登録実行': //新規登録データを編集エリアに追加する
                    $this->service->shopsAdd($this->dto);
                    $this->shopsVali->commonVali($this->dto);
                    break;

                case '修正実行':  //shopAltTblの内容をDBに反映する。     
                    $this->service->repoDataMake($this->dto);

                    $isError =   $this->shopsVali->commonVali($this->dto);

                    $_SESSION['shopAltTbl']   =    $this->dto->shopAltTbl;
                    //var_dump($this->dto->shopAltTbl);exit;                        

                    if(!$isError){
                        $this->service->shopsAlt($this->dto,$viewEditKey);
                        $_SESSION['shopAltTbl']   =   [];
                    }
                    break;

                case 'キャンセル':

                    $this->restoreEditingData($this->dto);
                    break;
            }
            $this->prepareNextRequest($this->dto);
            
        }
        $this->render();
    }
//
    private function render(){
        
        $tokenKey = generateCsrfToken();
        if(empty($this->dto->shopAltTbl??'[]')){
            $ShopList   =   $this->service->getShopsData($this->dto);
        }else{
            $ShopList   =   $this->dto->shopAltTbl??'[]';
        }
        require ROOT_PATH.'/views/shops/shopsView.php';
    }

    private function restoreEditingData(shopsDto $dto){    //すでに修正データがある場合、編集データにコピー

        $dto->shopAltTbl = !empty($_SESSION['shopAltTbl']) 
                            ? $_SESSION['shopAltTbl']                   //前トランの変更データがある時
                            : $dto->userShops;                          //変更データが存在しない時、初期読み込みデータを代入
        $_SESSION['shopAltTbl'] =   $dto->shopAltTbl;

    }

    private function prepareNextRequest(shopsDto $dto){    //次セッション、renderデータ準備
        //$dto->acctAltTbl = array_values($dto->acctAltTbl); 
        $_SESSION['shopAltTbl']   = $dto->shopAltTbl;
 
    }
}	