<?php
class homeController{
    public function index() {
        require_once ROOT_PATH . '/app/services/lib/homeLib.php';
        require_once ROOT_PATH . '/app/services/homeService.php';
        require_once ROOT_PATH . '/app/controllers/lib/auth.php';
        require_once ROOT_PATH . '/app/dto/homeDto.php';

        $dto = new homeDto([]);
        $messege = "";
        $dto->viewResult = [];
        // POST > SESSION > デフォルト の優先順位で確定させる           shopsデータが入っている。$_SESSION['user_shops']
        $dto->reportType = $_POST['reportType'] ?? $_SESSION['reportType'] ?? '月次試算表';
        // 次回のためにセッションを更新しておく
        $_SESSION['reportType'] = $dto->reportType;
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $dto->session   = $_SESSION;
            $dto->post      = $_POST;
			requireCsrf();
            requireLogin();
            if(!isset($dto->reportType)){
                $messege = "試算表の種類を選択してください。";
                require_once ROOT_PATH . '/views/login.php';
                exit;
            }

            $service = new homeServiceCls($dto->reportType );

            $service->homeService($dto);
            $dto->viewResult = $service->result;
            $dto->reportType = $service->reportType;
            $dto->from       = $service->from;
            $dto->to         = $service->to;
            $dto->zenki_from = $service->zenki_from;
            $dto->zenki_to   = $service->zenki_to;
        }
        $this->render( $dto );
    }
    function render( $dto ) {
        $tokenKey = generateCsrfToken();
                $today = new dateTime();
                $dto->nenji_nen = $dto->post['nenji_nen'] ?? $dto->nenji_nen ?? "";
                $lastDate = $today->modify('-1 month');               
                $dto->from = $dto->from ?? $lastDate->format('Y-m-d');
                $dto->to = $dto->to ?? date('Y-m-d');
                $result = [];
        require_once ROOT_PATH . '/views/homeView.php';
    }
}

