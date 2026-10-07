<?php
require_once ROOT_PATH . '/app/services/lib/homeLib.php';
require_once ROOT_PATH . '/app/services/homeService.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/dto/homeDto.php';
require_once ROOT_PATH . '/app/validators/homeValidator.php';

require_once ROOT_PATH . '/lib/helpers.php';

//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {

class homeController{

    private $pdo;
    private $validator;
    private $service;
    private $dto;

    public function __construct($pdo) {
        $this->pdo      = $pdo;
        $this->dto      = new homeDto([]);
        $this->validator = new homeValidator($this->dto, false);
        $this->service = new homeService($this->dto, $this->pdo );
    }

    public function index() {

//        $this->dto = new homeDto([]);
        $messege = "";
        $this->dto->viewResult = [];
        // POST > SESSION > デフォルト の優先順位で確定させる           shopsデータが入っている。$_SESSION['user_shops']
        $this->dto->reportType = $_POST['reportType'] ?? $_SESSION['reportType'] ?? '月次試算表';
        // 次回のためにセッションを更新しておく
        $_SESSION['reportType'] = $this->dto->reportType;

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->dto->session   = $_SESSION;
            $this->dto->post      = $_POST;
            $_SESSION['post']     = $_POST;
            requireCsrf();
            requireLogin();
            require_once ROOT_PATH . '/app/validators/homeValidator.php';
            $err = $this->validator->commonVali();
            if ($err > 0) {
                // バリデーションエラーがある場合は集計処理を行わず、レンダリングして終了
                $this->render();
                return;
            }

            $_SESSION['sisanStartEnd'] = $this->service->homeService();
            $this->dto->viewResult = $this->service->result;
            $this->dto->reportType = $this->service->reportType;
            $this->dto->from       = $this->service->from;
            $this->dto->to         = $this->service->to;
            $this->dto->zenki_from = $this->service->zenki_from;
            $this->dto->zenki_to   = $this->service->zenki_to;
        }
        $this->render();
    }
    function render() {
        $tokenKey = generateCsrfToken();
        $today = new dateTime();
  
        if($this->dto->reportType ?? $_SESSIOM['post']['reportType'] ?? [] === getujiSisanhyou){
            $this->dto->from = $this->dto->from ?: ($_SESSION['post']['from']??0);
            $_SESSION['post']['from'] = $this->dto->from;
        }
        if($this->dto->reportType ?? $_SESSIOM['post']['reportType'] ?? []  === nenjiSisanhyou){
            $this->dto->post['nenji_nen'] = $this->dto->post['nenji_nen'] ?? ($_SESSION['post']['nenji_nen'] ?? 0);
            $_SESSION['post']['nenji_nen'] = $this->dto->post['nenji_nen'];
        }
        if($this->dto->reportType ?? $_SESSIOM['post']['reportType'] ?? []  === ruisekiSisanhyou){
            $this->dto->to = $this->dto->to ?: ($_SESSION['post']['to'] ?? 0);
            $_SESSION['post']['to'] = $this->dto->to;
        }
        if($this->dto->reportType ?? $_SESSIOM['post']['reportType'] ?? []  === zenkiHikaku){
            $this->dto->post['kijyun_nen'] = $this->dto->post['kijyun_nen'] ?? ($_SESSION['post']['kijyun_nen'] ?? 0);
            $_SESSION['post']['kijyun_nen'] = $this->dto->post['kijyun_nen'];
        }
        if($this->dto->reportType ?? $_SESSIOM['post']['reportType'] ?? []  === kikanSisanhyou){
            $this->dto->from = $this->dto->from ?: ($_SESSION['post']['from'] ?? 0);
            $_SESSION['post']['from'] = $this->dto->from;
            $this->dto->to = $this->dto->to ?: ($_SESSION['post']['to'] ?? 0);
            $_SESSION['post']['to'] = $this->dto->to;
        }

        $lastDate = $today->modify('-1 month');               
        $result = [];
        $currentReport = $this->dto->reportType ?? $_SESSION['sisanStartEnd']['reportType'] ?? '';
        $viewType = "sisanhyou";
        require_once ROOT_PATH . '/views/homeView.php';
    }
}

