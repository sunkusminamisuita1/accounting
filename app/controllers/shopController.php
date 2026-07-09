<?php
require_once ROOT_PATH . '/app/DTO/ShopsDto.php';
class shopController{

	Public        $Service;
    public        $Dto;
    public        $ctrErrMsgPopUp;

	public function __construct()
    {
        $this->Dto   			=   new ShopsDto();
		$this->Dto->User		=	$_SESSION['user']??"";
		$this->Dto->ShopList	= 	$_SESSION['shoplist']??"";
        $this->Service   		=   new ShopsService($this->ctrDto);
        $this->ctrErrMsgPopUp 	= 	new ErrMsgPopUp($this->ctrDto);
		$this->Repo				=	new ShopsRepository();
    }

    public function switch()
	{
		$targetShopId = $_GET['shop_id'] ?? '';

		// 所有している店舗リストの中に、選択されたIDが存在するか安全チェック
		$validShop = false;
		if ($targetShopId === 'all') {
    		$validShop = true;
    		$_SESSION['current_shop_id'] = 'all';
    		$_SESSION['current_shop_name'] = '全店合算';
		} else {
    		foreach ($_SESSION['user_shops'] as $shop) {
		        if ($shop['id'] == $targetShopId) {
        		    $_SESSION['current_shop_id'] = $shop['id'];
		            $_SESSION['current_shop_name'] = $shop['shop_name'];
        		    $validShop = true;
		            break;
        		}
    		}
		}
		// 元のページ（またはホーム）に戻す
		$returnRoute = $_SESSION['current_route'] ?? 'home';
		header("Location: index.php?route={$returnRoute}");
		exit;
	}

	//shopデータ登録、更新
    public function edit()
    {
		//ProcSlct.phpでShopCodeが変更できるため、最新のShopCodeをサービスに設定
		$this->Service->RenewTartgetShopCode($Dto)

		if( ! $this->Dto->ShopList??""){
			$Dto->ShopList = $this->Repo->getShopsByUserId($Dto);
			$this->Service->GetShops($this->ctrDto);
		}
        if( ! $this->Dto->Accounts){
            $this->Service->GetAccounts($this->ctrDto);
        }

        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            $this->ctrDto->PostDt = $_POST;
            $ViewEditKey = $_POST['ViewEditKey'] ?? null;
            switch($_POST['AcctPfm']){

                case '追加':
                    $this->RestoreEditingData($this->ctrDto);
                    $this->Service->AccountsAdd($this->ctrDto);
                    $this->PrepareNextRequest($this->ctrDto);
                    break;

                case '削除':  //削除ボタンは、削除フラグのon offを切り替え,AcctAltTblのis_deleted,errmsg,edittypeを更新
                    $this->RestoreEditingData($this->ctrDto);
                    $this->Service->AccountsEdit($this->ctrDto,$ViewEditKey);
                    $this->PrepareNextRequest($this->ctrDto);
                    break;

                case '修正実行':  //AcctAltTblの内容をDBに反映する。                  
                    $this->RestoreEditingData($this->ctrDto);
                    $this->Service->RepoDataMake($this->ctrDto);
                    $this->Service->AccountsAlt($this->ctrDto,$ViewEditKey);
                    $this->PrepareNextRequest($this->ctrDto);
                    break;

                case 'キャンセル':
                    $this->Service->AccountsCancel($this->ctrDto);
                    break;
            }
            
        }

            $TokenKey = generateCsrfToken();
            $Accounts   =   $this->ctrDto->AcctAltTbl;
        require ROOT_PATH.'/views/Accounts/AccountsView.php';
    }

    private function RestoreEditingData(AccountsDto $Dto){    //すでに修正データがある場合、編集データにコピー

        if($_SESSION['Accounts'] ?? ""){    
            $Dto->AcctAltTbl = $_SESSION['Accounts'];
            unset($_SESSION['Accounts']);
        }  

    }

    private function PrepareNextRequest(AccountsDto $Dto){    //次セッション、renderデータ準備
        //$Dto->AcctAltTbl = array_values($Dto->AcctAltTbl); 
        $_SESSION['Accounts']   = $Dto->AcctAltTbl;
 
    }
}	