<?php
class HomeController{
    public function index() {
        require_once ROOT_PATH . '/app/services/lib/HomeLib.php';
        require_once ROOT_PATH . '/app/services/HomeService.php';
        require_once ROOT_PATH . '/app/controllers/lib/auth.php';
        $messege = "";
        $ViewResult = [];
        // POST > SESSION > デフォルト の優先順位で確定させる
        $ReportType = $_POST['ReportType'] ?? $_SESSION['ReportType'] ?? '月次試算表';
        // 次回のためにセッションを更新しておく
        $_SESSION['ReportType'] = $ReportType;
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			requireCsrf();
            requireLogin();
            if(!isset($ReportType)){
                $messege = "試算表の種類を選択してください。";
                require_once ROOT_PATH . '/views/auth/login.php';
                exit;
            }
            $HmSvcInstance = new HomeServiceCls($ReportType);
            $HmSvcInstance->HomeService();
            $ViewResult = $HmSvcInstance->result;
            $ReportType = $HmSvcInstance->ReportType;
            $from = $HmSvcInstance->from;
            $to = $HmSvcInstance->to;
            $zenki_from = $HmSvcInstance->zenki_from;
            $zenki_to = $HmSvcInstance->zenki_to;
        }
        $TokenKey  = generateCsrfToken();
    //    $TokenTime = $_SESSION['csrfTokens'][$TokenKey] ?? '';
        require_once ROOT_PATH . '/views/home/HomeView.php';
    }
}

