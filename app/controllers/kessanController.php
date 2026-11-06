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
        //$this->validator = new plValidator($this->dto, false);
        $this->service = new kessanService($this->dto, $this->pdo );
    }

    public function report($controllerName, $method) {
        $this->dto->reportType     = $_POST['reportType'] ?? $_SESSION['reportType'] ?? '経費一覧';
        $this->dto->reportSubType  = $_POST['reportSubType'] ?? $_SESSION['reportSubType'] ?? '月次';
        $_SESSION['reportType']    = $this->dto->reportType ?? '';
        $_SESSION['reportSubType'] = $this->dto->reportSubType ?? '';
        echo "reportType: " . $this->dto->reportType . "<br>";
        echo "reportSubType: " . $this->dto->reportSubType . "<br>";
        $reportType = $this->dto->reportType;
        $reportSubType = $this->dto->reportSubType;
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->dto->session   = $_SESSION;
            $this->dto->post      = $_POST;
            $_SESSION['post']     = $_POST;
            requireCsrf();
            requireLogin();
            //dbg('kessanController//report', $this->dto->session);
            if(
                ( ($this->dto->session['reportType']??'')            ===   '経費一覧')     &&
                ( ($this->dto->session['reportSubType']??'')         !==   ''        )  
              )
            {
                $this->validator = new kessanValidator($this->dto, false);
                $err = $this->validator->kessanItiran();
                if ($err) {
                    $this->render($controllerName, $method);
                    return $err;
                }
                
                $getujiKeihiItiran = $this->service->keihiItiran();
                $err = $this->validator->noDataChk($getujiKeihiItiran);
                 if ($err) {
                    $this->render($controllerName, $method);
                    return $err;
                }               

                $this->render($controllerName, $method);
            exit;
            }

            if($this->dto->post['reportType']??'' === 'pl'){
                echo "損益計算書";
            }
            
        }
        $this->render($controllerName, $method);
    }
    function render($controllerName, $method) {
        $tokenKey = generateCsrfToken();
        $today = new dateTime();
  
        if($this->dto->reportSubType ?? $_SESSIOM['post']['reportSubType'] ?? [] === '月次'){
            $this->dto->from = $this->dto->from ?: ($_SESSION['post']['from']??0);
            $_SESSION['post']['from'] = $this->dto->from;
        }
        if($this->dto->reportSubType ?? $_SESSIOM['post']['reportSubType'] ?? []  === '年次'){
            $this->dto->post['nenji_nen'] = $this->dto->post['nenji_nen'] ?? ($_SESSION['post']['nenji_nen'] ?? 0);
            $_SESSION['post']['nenji_nen'] = $this->dto->post['nenji_nen'];
        }
        if($this->dto->reportSubType ?? $_SESSIOM['post']['reportSubType'] ?? []  === '累積'){
            $this->dto->to = $this->dto->to ?: ($_SESSION['post']['to'] ?? 0);
            $_SESSION['post']['to'] = $this->dto->to;
        }
        if($this->dto->reportSubType ?? $_SESSIOM['post']['reportSubType'] ?? []  === '前期比較'){
            $this->dto->post['kijyun_nen'] = $this->dto->post['kijyun_nen'] ?? ($_SESSION['post']['kijyun_nen'] ?? 0);
            $_SESSION['post']['kijyun_nen'] = $this->dto->post['kijyun_nen'];
        }
        if($this->dto->reportSubType ?? $_SESSIOM['post']['reportSubType'] ?? []  === '期間'){
            $this->dto->from = $this->dto->from ?: ($_SESSION['post']['from'] ?? 0);
            $_SESSION['post']['from'] = $this->dto->from;
            $this->dto->to = $this->dto->to ?: ($_SESSION['post']['to'] ?? 0);
            $_SESSION['post']['to'] = $this->dto->to;
        }

        $lastDate = $today->modify('-1 month');               
        $result = [];
        $currentReport    = $this->dto->reportType ?? $_SESSION['kessanStartEnd']['reportType'] ?? '';
        $currentSubReport = $this->dto->reportSubType ?? $_SESSION['kessanStartEnd']['reportSubType'] ?? '';
        $viewType = "sisanhyou";
        require_once ROOT_PATH . '/views/homeView.php';
    }
}

