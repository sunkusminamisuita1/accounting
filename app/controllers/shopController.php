<?php
require_once ROOT_PATH . '/app/dto/shopsDto.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/validators/ShopsValidator.php';

class shopController{

	Public        $Service;
    public        $dto;
    public        $ctrerrMsgPopUp;
	public		  $Repo;
	//public		  $ctrerrMsgPopUp;
    private       $ShopsVali;

	public function __construct()
    {
        $DebugMode = 'false';
        $this->dto   			=   new shopsDto();
		$this->dto->user		=	$_SESSION['user']??"";

		$this->dto->ShopAltTbl	= 	empty($_SESSION['ShopAltTbl'])
                                    ? $_SESSION['UserShops']
                                    : $_SESSION['ShopAltTbl'] ;
        $_SESSION['ShopAltTbl'] =   $this->dto->ShopAltTbl;

        $this->Service   		=   new ShopsService($this->dto);
        $this->ctrerrMsgPopUp 	= 	new errMsgPopUp($this->dto);
		$this->Repo				=	new shopsRepository();
		$this->ctrerrMsgPopUp   =   new errMsgPopUp($this->dto);
        $this->ShopsVali        =   new ShopsValidator('');
		
    }

    public function switch()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$targetShopId = $_POST['active_shop'] ?? '';

			// 所有している店舗リストの中に、選択されたIDが存在するか安全チェック
			$validShop = false;
			if ($targetShopId === '   all') {
				$validShop = true;
				$_SESSION['current_shop_code'] = '   all';
				$_SESSION['current_shop_name'] = '全店合算';
			} else {

				foreach ($_SESSION['ShopAltTbl'] as $i=>$shop) {

					if ((string)$shop['shop_code'] === $targetShopId) {
						$_SESSION['current_shop_code'] = (string)$shop['shop_code'];
						$_SESSION['current_shop_name'] = (string)$shop['shop_name'];
						$validShop = true;
						break;
					}
				}
				if(!$validShop){
					echo "<br>err shopcontoroller.switch 入力shop_idがありません";exit;
				}

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

			//ProcSlct.phpでShopCodeが変更できるため、最新のShopCodeをサービスに設定
			$this->Service->RenewTargetShopCode($this->dto);

            $this->dto->postDt = $_POST ?? '';

            $viewEditKey = $_POST['viewEditKey'] ?? null; //修正表　行インデックス

            $this->restoreEditingData($this->dto);

            $_SESSION['isValidated']    =   '';

            switch($_POST['ShopsPfm']){

                case '登録実行': //新規登録データを編集エリアに追加する
                    $this->Service->ShopsAdd($this->dto);
                    $this->ShopsVali->CommonVali($this->dto);
                    break;

                case '修正実行':  //ShopAltTblの内容をDBに反映する。     
                    $this->Service->RepoDataMake($this->dto);

                    $isError =   $this->ShopsVali->CommonVali($this->dto);

                    $_SESSION['ShopAltTbl']   =    $this->dto->ShopAltTbl;
                    //var_dump($this->dto->ShopAltTbl);exit;                        

                    if(!$isError){
                        $this->Service->ShopsAlt($this->dto,$viewEditKey);
                        $_SESSION['ShopAltTbl']   =   [];
                    }
                    break;

                case 'キャンセル':

                    $this->restoreEditingData($this->dto);
                    break;
            }
            $this->prepareNextRequest($this->dto);
            
        }
        $this->Render();
    }
//
    private function render(){
        
        $tokenKey = generateCsrfToken();
        if(empty($this->dto->ShopAltTbl??'[]')){
            $ShopList   =   $this->Service->getShopsData($this->dto);
        }else{
            $ShopList   =   $this->dto->ShopAltTbl??'[]';
        }
        require ROOT_PATH.'/views/Shops/ShopsView.php';
    }

    private function restoreEditingData(shopsDto $dto){    //すでに修正データがある場合、編集データにコピー

        $dto->ShopAltTbl = !empty($_SESSION['ShopAltTbl']) 
                            ? $_SESSION['ShopAltTbl']                   //前トランの変更データがある時
                            : $dto->userShops;                          //変更データが存在しない時、初期読み込みデータを代入
        $_SESSION['ShopAltTbl'] =   $dto->ShopAltTbl;

    }

    private function prepareNextRequest(shopsDto $dto){    //次セッション、renderデータ準備
        //$dto->acctAltTbl = array_values($dto->acctAltTbl); 
        $_SESSION['ShopAltTbl']   = $dto->ShopAltTbl;
 
    }
}	