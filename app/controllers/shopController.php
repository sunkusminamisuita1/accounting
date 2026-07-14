<?php
require_once ROOT_PATH . '/app/DTO/ShopsDto.php';
require_once ROOT_PATH . '/lib/helpers.php';
class shopController{

	Public        $Service;
    public        $Dto;
    public        $ctrErrMsgPopUp;
	public		  $Repo;
	public		  $CtrErrMsgPopUp;

	public function __construct()
    {
        $this->Dto   			=   new ShopsDto();
		$this->Dto->User		=	$_SESSION['user']??"";
		$this->Dto->UserShops	= 	$_SESSION['ShopAltTbl']??[];
        $this->Service   		=   new ShopsService($this->Dto);
        $this->ctrErrMsgPopUp 	= 	new ErrMsgPopUp($this->Dto);
		$this->Repo				=	new ShopsRepository();
		$this->CtrErrMsgPopUp   =   new ErrMsgPopUp($this->Dto);
		
    }

    public function switch()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			//$targetShopId = $_GET['shop_id'] ?? '';
			$targetShopId = $_POST['active_shop'] ?? '';
			//echo "targetShopId={$targetShopId}";exit;

			// 所有している店舗リストの中に、選択されたIDが存在するか安全チェック
			$validShop = false;
			if ($targetShopId === 'all') {
				$validShop = true;
				$_SESSION['current_shop_code'] = 'all';
				$_SESSION['current_shop_name'] = '全店合算';
			} else {

				foreach ($_SESSION['UserShops'] as $i=>$shop) {

					var_dump((int)$shop['shop_code']);
					echo "<br>targetShopId=";var_dump((int)$targetShopId);


					if ((int)$shop['shop_code'] === (int)$targetShopId) {
						$_SESSION['current_shop_code'] = $shop['shop_code'];
						$_SESSION['current_shop_name'] = $shop['shop_name'];
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
			header("Location: index.php?route={$returnRoute}");
			exit;
		}
	}

	//shopデータ登録、更新
    public function edit()
    {
        $this->Dto->User = $_SESSION['user'] ?? '';

        // 店舗データの初期キャッシュ処理（emptyで安全に判定）
        if (empty($this->Dto->UserShops)) {
            $_SESSION['UserShops'] = $this->Service->getShopsData($this->Dto);
            $this->Dto->UserShops  = $_SESSION['UserShops'];
        }
        //$this->RestoreEditingData($this->Dto);


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		    requireCsrf();

			//ProcSlct.phpでShopCodeが変更できるため、最新のShopCodeをサービスに設定
			$this->Service->RenewTargetShopCode($this->Dto);

            $this->Dto->PostDt = $_POST ?? '';
            $ViewEditKey = $_POST['ViewEditKey'] ?? null; //修正表　行インデックス

            $this->RestoreEditingData($this->Dto);
            switch($_POST['ShopsPfm']){

                case '追加':
                    $this->Service->ShopsAdd($this->Dto);
                    break;

                case '削除':  //削除ボタンは、削除フラグのon offを切り替え,AcctAltTblのis_deleted,errmsg,edittypeを更新
                    $this->Service->ShopsEdit($this->Dto,$ViewEditKey);
                    break;

                case '修正実行':  //ShopAltTblの内容をDBに反映する。                  
                    $this->Service->RepoDataMake($this->Dto);
                    $this->Service->ShopsAlt($this->Dto,$ViewEditKey);
                    break;

                case 'キャンセル':
                    $this->Service->AccountsCancel($this->Dto);
                    break;
            }
            $this->PrepareNextRequest($this->Dto);
            
        }

            $TokenKey = generateCsrfToken();
            if(empty($this->Dto->ShopAltTbl??'[]')){
                $ShopList   =   $this->Service->getShopsData($this->Dto);
            }else{
                $ShopList   =   $this->Dto->ShopAltTbl??'[]';
            }
        require ROOT_PATH.'/views/Shops/ShopsView.php';
    }

    private function RestoreEditingData(ShopsDto $Dto){    //すでに修正データがある場合、編集データにコピー
        if(!empty($Dto->ShopAltTbl)){                      //すでに変更データが存在する時、
            echo "<br>shopController.edit 論理エラー　処理前に変更データが存在します。";
            exit;
        }

        $Dto->ShopAltTbl = !empty($_SESSION['ShopAltTbl']) 
            ? $_SESSION['ShopAltTbl']                   //前トランの変更データがある時
            : $Dto->UserShops;                          //変更データが存在しない時、初期読み込みデータを代入

        unset($_SESSION['ShopAltTbl']);
    }

    private function PrepareNextRequest(ShopsDto $Dto){    //次セッション、renderデータ準備
        //$Dto->AcctAltTbl = array_values($Dto->AcctAltTbl); 
        $_SESSION['ShopAltTbl']   = $Dto->ShopAltTbl;
 
    }
}	