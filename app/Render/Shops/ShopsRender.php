<?php 
    public function render($Dto,$Service){
        $TokenKey = generateCsrfToken();
        if(empty($Dto->ShopAltTbl??'[]')){
            $ShopList   =   $Service->getShopsData($this->Dto);
        }else{
            $ShopList   =   $Dto->ShopAltTbl??'[]';
        }
        require ROOT_PATH.'/views/Shops/ShopsView.php';
    }
?>