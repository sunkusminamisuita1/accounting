<?php
class homeController{
    public function index() {
        require_once ROOT_PATH . '/app/services/lib/homeLib.php';
        require_once ROOT_PATH . '/app/services/homeService.php';
        require_once ROOT_PATH . '/app/controllers/lib/auth.php';
        require_once ROOT_PATH . '/app/dto/homeDto.php';

        $dto = new homeDto([]);
        $messege = "";
        $viewResult = [];
        // POST > SESSION > デフォルト の優先順位で確定させる           shopsデータが入っている。$_SESSION['user_shops']
        $reportType = $_POST['reportType'] ?? $_SESSION['reportType'] ?? '月次試算表';
        // 次回のためにセッションを更新しておく
        $_SESSION['reportType'] = $reportType;
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $dto->session   = $_SESSION;
            $dto->post      = $_POST;
			requireCsrf();
            requireLogin();
            if(!isset($reportType)){
                $messege = "試算表の種類を選択してください。";
                require_once ROOT_PATH . '/views/auth/login.php';
                exit;
            }
            //$dto->activShop  = $_SESSION['current_shop_code'] ?? '   all';
            $dto->activeShop = $dto->session['activeShopCode'] ?? '   all';
            echo "dto->activeShop : {$dto->activeShop}";
            $service = new homeServiceCls($reportType );

            $service->homeService($dto);
            $viewResult = $service->result;
            $reportType = $service->reportType;
            $from       = $service->from;
            $to         = $service->to;
            $zenki_from = $service->zenki_from;
            $zenki_to   = $service->zenki_to;
        }
        $this->render($reportType, $from, $to, $zenki_from, $zenki_to, $viewResult);
    }
    function render($reportType, $from, $to, $zenki_from, $zenki_to, $viewResult) {
        $tokenKey = generateCsrfToken();
        require_once ROOT_PATH . '/views/homeView.php';
    }
}

