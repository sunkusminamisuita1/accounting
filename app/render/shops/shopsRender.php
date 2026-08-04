<?php 
    public function render($dto,$Service){
        $tokenKey = generateCsrfToken();
        if(empty($dto->ShopAltTbl??'[]')){
            $ShopList   =   $Service->getShopsData($this->dto);
        }else{
            $ShopList   =   $dto->ShopAltTbl??'[]';
        }
        require ROOT_PATH.'/views/Shops/ShopsView.php';
    }
?>