<?php 
    function render($dto,$service){
        $tokenKey = generateCsrfToken();
        if(empty($dto->shopAltTbl??'[]')){
            $ShopList   =   $service->getShopsData($dto);
        }else{
            $ShopList   =   $dto->shopAltTbl??'[]';
        }
        require ROOT_PATH.'/views/Shops/ShopsView.php';
    }
?>