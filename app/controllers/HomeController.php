<?php
class HomeController{
    private $service;
    public function __construct() {
        $this->service = new ReportService();
    }
    public function index() {
        require_once ROOT_PATH . '/app/services/lib/HomeLib.php';
        require_once ROOT_PATH . '/app/services/HomeService.php';
        require_once ROOT_PATH . '/app/auth.php';
        $messege = "";
        $ReportType = $_POST['ReportType'] ?? '月次試算表';
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			requirePost();
            requireLogin();
            verifyCsrfToken($_POST['csrfTokenKey'] ?? '');
            if(!isset($_POST['ReportType'])){
                $messege = "試算表の種類を選択してください。";
                require_once ROOT_PATH . '/views/auth/login.php';
                exit;
            }
            $ReportType = $_POST['ReportType'] ?? '月次試算表';
            echo "2222222222222222222<br>";
            $HmSvcInstance = new HomeServiceCls($ReportType);
            echo "1111111111111111111<br>";
            var_dump($_POST);
            $HmSvcInstance->HomeService();
            echo "////////////////////////////////////////////////<br>";
            var_dump($_POST);
            if(isset($_POST['KeisanJikkou']) && $_POST['KeisanJikkou'] === "Exec"){
 //               if($_POST['from'] || $_POST['to'] || $_POST['nenji_nen'] || $_POST['kijyun_nen']){
                    $HmSvcInstance->HomeService();
 //               }
            }
        }
        $TokenKey  = generateCsrfToken();
        $TokenTime = $_SESSION['csrfTokens'][$TokenKey] ?? '';
        require_once ROOT_PATH . '/views/home/HomeView.php';
    }
}

