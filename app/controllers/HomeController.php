<?php
class HomeController{
    private $service;
    public function __construct() {
        $this->service = new ReportService();
    }
    public function index() {
        $ReportType = $_POST['ReportType'] ?? '月次試算表';
        $messege = "";
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			requirePost();
            requireLogin();
            verifyCsrfToken($_POST['csrfToken'] ?? '');
            if(!isset($_POST['ReportType'])){
                $messege = "試算表の種類を選択してください。";
                require_once ROOT_PATH . '/view/Auth/Login.php';
                exit;
            }else{
                $_SESSION['HomeController']['ReportType'] = $_POST['ReportType'];
                $RepotType = $_POST['ReportType'];
                $startEnd = StartEnd(GetujiSisanhyou);
                if(GetujiSisanhyou === $RepotType){
                    echo "HomeController debug ";exit;
                }
                require_once ROOT_PATH . '/app/service/HomeService.php';
            }
        }
        require_once ROOT_PATH . '/views/home/HomeView.php';
    }
}

