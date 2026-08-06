<?php
require_once ROOT_PATH . '/app/services/voucherService.php';
require_once ROOT_PATH . '/app/dto/voucherDto.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/validators/voucherValidator.php';
require_once ROOT_PATH . '/app/repositories/voucherRepository.php';

class voucherController
{
    private voucherService $service;
    private voucherDto $dto;
    private voucherValidator $validator;
    private voucherRepository $repo;
    private errMsgPopUp $errMsgPopUp;
    private string $renderType;
    private $tokenKey;

    public function __construct()  {
        $this->dto = new voucherDto([]);
        $this->service = new voucherService();
        $this->repo = new voucherRepository();
        $this->validator = new voucherValidator();
        $this->errMsgPopUp = new errMsgPopUp();
        $this->dto->accounts = $this->service->getaccounts();
    }
    public function create(): void    {
        //file_put_contents('/var/www/html/test6/public/debug.log', "メソッド通ったよ！\n", FILE_APPEND);
        $this->dto->vcrCreData();                           //dtoにPOSTされた明細行を渡す
        $details = $this->dto->dtoDetails;                  //dtoから明細行を取得
        $accounts = $this->dto->accounts;
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            requireCsrf();
            $this->service->vcrCreate($this->dto);
            // POST を処理した後は再描画用に新しいトークンを発行する
            $this->tokenKey = generateCsrfToken();
        }else{
            $this->tokenKey  = generateCsrfToken();          
        }
        $this->render('create');
    }
    
    public function store(): void {
        requirePost();
        $data = $_POST;
        
        foreach ($_POST['details'] as $i => $d) {
            $details[] = [
                'account_id' => (int)$d['account_id'],
                'amount' => (int)$d['amount'],
                'side' => $d['side'],
                'line_no' => $i
            ];
        }

        $data = [
            'date' => $_POST['voucher_date'],
            'summary' => $_POST['summary'],
            'user_id' => getLoginUserId(),
            'details' => $details
        ];

        //$this->service->saveVoucher($data);
        $this->service->vcrSave($this->dto,$this->validator);
        header('location: index.php?route=voucher.index');
        exit;
    }
    // 一覧
    public function index() {
        $userId = getLoginUserId();
        $vouchers = $this->service->list($userId);
        require ROOT_PATH.'/views/voucher/index.php';
    }

    // 修正、削除データ検索
    public function list() {
        $this->dto->list(); //dtoのListメソッドで検索条件をセット
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            requireCsrf();                              //CSRFトークンの検証
            $this->dto->list(); //dtoのListメソッドで検索条件をセット 未入力の場合はセッションから検索条件をセットするため、POSTされた検索条件をDtoにセットする前にList()メソッドを呼び出す必要があります。

            if (isset($_POST['simpleSearch'])) {        //修正データ一覧作成
                $this->service->vcrsimpleSearch($this->dto , $this->repo, $this->validator);
            }
            if (isset($_POST['vcrUpdateNo'])) {         //修正対象データ　編集用データ作成
                $this->service->vcrUpdNo($this->dto, $this->repo, $this->validator);
            }
            //前回の行追加、行削除の処理は、修正対象データの編集用データ作成の後に行う必要があり、
            //行追加、行削除の処理は、編集用データを基に行う必要があるためです。
            //もし、行追加、行削除の処理を先に行ってしまうと、
            //編集用データがまだ作成されていない状態で行追加、行削除の処理が行われてしまい、正しく処理できなくなってしまいます。
            //if( isset($_POST['vcrAddDebit'])  || isset($_POST['vcrAddCredit'])  || isset($_POST['vcrDetailLineDel'])) {
            //    $this->service->vcrSearchedDataRemake($this->dto , $this->repo, $this->validator);
            //}
            if( isset($_POST['vcrAddDebit'])) {         //行追加ボタン（借方）を押したときの処理
                $this->service->vcrAddDebit($this->dto, $this->repo, $this->validator);
            }
            if( isset($_POST['vcrAddCredit'])) {        //行追加ボタン（貸方）を押したときの処理
                $this->service->vcrAddCredit($this->dto, $this->repo, $this->validator);
            }
            if( isset($_POST['vcrDetailLineDel'])) {    //仕分け編集データから　一行削除
                $this->service->vcrDetailLineDel($this->dto, $this->repo, $this->validator);
            }
            if( isset($_POST['vcrDelete'])) {           //1仕分け伝票削除
                $Success = $this->service->vcrDelete($this->dto, $this->repo, $this->validator);
                if ($Success) {
                    //file_put_contents('/var/www/html/test6/public/debug.log', "Success2 = {$Success}！\n", FILE_APPEND);
                    // 3. ユーザーへの完了通知メッセージだけをセッションに仕込む
                    $_SESSION['flash_message'] = "伝票を正常に削除しました。";        
                    // 4. そのまま一覧画面（または新規作成画面）へ一発リダイレクト！
                    //header('location: index.php?route=voucher.list'); //リダイレクトしてPOSTデータの再送信を防止
                    //exit;
                }
            }
            if( isset($_POST['vcrUpdate'])) {           //1仕分け伝票データ　DB更新
                $Success = $this->service->vcrUpdate($this->dto, $this->repo, $this->validator);
            }

            // POST を処理した後は再描画用に新しいトークンを発行する
            $this->tokenKey = generateCsrfToken();
        }else{
            $this->tokenKey  = generateCsrfToken();
        }
        $this->render('list');

    }

    // 編集画面
    public function edit(){
        $id = (int)($_GET['id'] ?? 0);
        $voucher = $this->service->find($id);
        require ROOT_PATH.'/views/voucher/edit.php';
    }

    // 更新
    public function update() {
        requirePost();
        $id = (int)$_POST['id'];
        $data = [
            'date' => $_POST['date'],
            'summary' => $_POST['summary']
        ];
        $this->service->update($id, $data);
        header('location: index.php?route=voucher.index');
        exit;
    }
    private function render($renderType) {
        $vcrListResult = $dto->vcrListResult ?? [];
        $accounts = $this->dto->accounts ?? [];
        $tokenKey = $this->tokenKey;
        if($renderType === 'create'){

            require ROOT_PATH . '/views/voucher/create.php';
            return 1;
        }
        if($renderType === 'list'){
            require ROOT_PATH.'/views/voucher/list.php';
            return 1;
        }
    }

}