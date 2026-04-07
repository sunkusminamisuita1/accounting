<?php
class HomeController{
    private $service;
    public function __construct() {
        $this->service = new ReportService();
    }
    public function index() {
        echo "homecontroller1<br>";
        require_once ROOT_PATH . '/app/services/lib/HomeLib.php';
        require_once ROOT_PATH . '/app/services/HomeService.php';
        require_once ROOT_PATH . '/app/auth.php';
        $messege = "";
        $ViewResult = [];


//        $_SESSION['ReportType'] = $_POST['ReportType'] ?? '月次試算表';
//        $ReportType = $_POST['ReportType'] ?? $_SESSION['ReportType'];

        // POST > SESSION > デフォルト の優先順位で確定させる
        $ReportType = $_POST['ReportType'] ?? $_SESSION['ReportType'] ?? '月次試算表';
        // 次回のためにセッションを更新しておく
        $_SESSION['ReportType'] = $ReportType;



        echo "homecontroller2<br>";
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo "homecontroller3<br>";
			requirePost();
            echo "homecontroller4<br>";
            requireLogin();
            echo "homecontroller5<br>"; 
            verifyCsrfToken($_POST['csrfTokenKey'] ?? '');
            echo "homecontroller6<br>"; 
            if(!isset($ReportType)){
                echo "homecontroller7<br>";
                $messege = "試算表の種類を選択してください。";
                require_once ROOT_PATH . '/views/auth/login.php';
                exit;
            }
            echo "2222222222222222222<br>";
            $HmSvcInstance = new HomeServiceCls($ReportType);
            echo "1111111111111111111<br>";
            var_dump($_POST);
 //           $HmSvcInstance->HomeService();
            echo "////////////////////////////////////////////////<br>";
            var_dump($_POST);
            if(isset($_POST['KeisanJikkou']) && ($_POST['KeisanJikkou'] === "Exec")){
                echo "3333333333333333333<br>";
                $_SESSION['ReportType'] = "";
                $HmSvcInstance->HomeService();
                $ViewResult = $HmSvcInstance->result;
                $ReportType = $HmSvcInstance->ReportType;
                $from = $HmSvcInstance->from;
                $to = $HmSvcInstance->to;
                $zenki_from = $HmSvcInstance->zenki_from;
                $zenki_to = $HmSvcInstance->zenki_to;
                echo "homecontroller8<br>";
                echo "result=";print_r($result);echo "<br>";
                echo "ReportType={$ReportType} from={$from} to={$to} zenki_from={$zenki_from} zenki_to={$zenki_to}<br>";
            }
        }
        echo "44444444444<br>";
        $TokenKey  = generateCsrfToken();
        $TokenTime = $_SESSION['csrfTokens'][$TokenKey] ?? '';
        require_once ROOT_PATH . '/views/home/HomeView.php';
    }
}

