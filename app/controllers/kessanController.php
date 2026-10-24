<?php
require_once ROOT_PATH . '/app/services/lib/homeLib.php';
require_once ROOT_PATH . '/app/services/kessanService.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/dto/kessanDto.php';
require_once ROOT_PATH . '/app/validators/kessanValidator.php';

require_once ROOT_PATH . '/lib/helpers.php';

//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {

class kessanController{

    private $pdo;
    private $validator;
    private $service;
    private $dto;

    public function __construct($pdo) {
        $this->pdo      = $pdo;
        $this->dto      = new kessanDto([]);
        $this->validator = new kessanValidator($this->dto, false);
        $this->service = new kessanService($this->dto, $this->pdo );
    }

    public function report($controllerName, $method) {

//        $this->dto = new kessanDto([]);
        $messege = "";
        $this->dto->viewResult = [];
        // POST > SESSION > デフォルト の優先順位で確定させる           shopsデータが入っている。$_SESSION['user_shops']
        $this->dto->reportType = $_POST['reportType'] ?? $_SESSION['reportType'] ?? '損益計算書';
        // 次回のためにセッションを更新しておく
        $_SESSION['reportType'] = $this->dto->reportType;

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->dto->session   = $_SESSION;
            $this->dto->post      = $_POST;
            $_SESSION['post']     = $_POST;
            requireCsrf();
            requireLogin();
            if($this->dto->post['reportType'] === '経費一覧'){echo "経費一覧";}
            exit;  
            
            






            
            require_once ROOT_PATH . '/app/validators/kessanValidator.php';
            $err = $this->validator->commonVali();
            if ($err > 0) {
                // バリデーションエラーがある場合は集計処理を行わず、レンダリングして終了
                $this->render($controllerName, $method);
                return;
            }

            $_SESSION['sisanStartEnd'] = $this->service->kessanService();
            $this->dto->viewResult = $this->service->result;
            $this->dto->reportType = $this->service->reportType;
            $this->dto->from       = $this->service->from;
            $this->dto->to         = $this->service->to;
            $this->dto->zenki_from = $this->service->zenki_from;
            $this->dto->zenki_to   = $this->service->zenki_to;
        }
        $this->render($controllerName, $method);
    }
    function render($controllerName, $method) {
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

