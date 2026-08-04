<?php
class homeController{
    public function index() {
        require_once ROOT_PATH . '/app/services/lib/homeLib.php';
        require_once ROOT_PATH . '/app/services/HomeService.php';
        require_once ROOT_PATH . '/app/controllers/lib/auth.php';
        $messege = "";
        $viewResult = [];
        // POST > SESSION > デフォルト の優先順位で確定させる           shopsデータが入っている。$_SESSION['user_shops']
        $reportType = $_POST['reportType'] ?? $_SESSION['reportType'] ?? '月次試算表';
        // 次回のためにセッションを更新しておく
        $_SESSION['reportType'] = $reportType;
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			requireCsrf();
            requireLogin();
            if(!isset($reportType)){
                $messege = "試算表の種類を選択してください。";
                require_once ROOT_PATH . '/views/auth/login.php';
                exit;
            }
            $hmSvcInstance = new homeServiceCls($reportType);
            $hmSvcInstance->HomeService();
            $viewResult = $hmSvcInstance->result;
            $reportType = $hmSvcInstance->reportType;
            $from = $hmSvcInstance->from;
            $to = $hmSvcInstance->to;
            $zenki_from = $hmSvcInstance->zenki_from;
            $zenki_to = $hmSvcInstance->zenki_to;
            //$tokenKey = $_POST['csrfTokenKey'];
            //}else{
            //    $tokenKey  = generateCsrfToken();
        }
        $tokenKey = generateCsrfToken();
    //    $tokenTime = $_SESSION['csrfTokens'][$tokenKey] ?? '';
        require_once ROOT_PATH . '/views/home/homeView.php';
    }
}

