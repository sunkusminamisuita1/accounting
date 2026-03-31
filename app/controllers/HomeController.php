<?php
class HomeController{
    private $service;
    public function __construct() {
        $this->service = new ReportService();
    }
    public function index() {
        require_once ROOT_PATH . '/app/services/HomeService.php';
        require_once ROOT_PATH . '/app/auth.php';
        $messege = "";
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			requirePost();
            requireLogin();
            verifyCsrfToken($_POST['csrfTokenKey'] ?? '');
            if(!isset($_POST['ReportType'])){
                $messege = "試算表の種類を選択してください。";
                require_once ROOT_PATH . '/views/auth/Login.php';
                exit;
            }else{
                $_SESSION['HomeController']['ReportType'] = $_POST['ReportType'];
                $RepotType = $_POST['ReportType'];
                $startEnd = StartEnd(GetujiSisanhyou);
                if(GetujiSisanhyou === $RepotType){
                    echo "HomeController debug ";exit;
                }
            }
        }
        $token = generateCsrfToken();
        $_SESSION['TokenKey'] = $token;
        require_once ROOT_PATH . '/views/home/HomeView.php';
    }
}
//
