<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';
require_once ROOT_PATH . '/app/DTO/VoucherDTO.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/Validators/VoucherValidator.php';
require_once ROOT_PATH . '/app/repositories/voucherRepository.php';

class VoucherController
{
    private VoucherService $Service;
    private VoucherDTO $Dto;
    private VoucherValidator $Validator;
    private VoucherRepository $Repo;
    private ErrMsgPopUp $ErrMsgPopUp;
    private string $RenderType;
    public function __construct()  {
        $this->Dto = new VoucherDTO([]);
        $this->Service = new VoucherService();
        $this->Repo = new VoucherRepository();
        $this->Validator = new VoucherValidator();
        $this->ErrMsgPopUp = new ErrMsgPopUp();
        $this->Dto->Accounts = $this->Service->getAccounts();
    }
    public function create(): void    {
        file_put_contents('/var/www/html/test6/public/debug.log', "メソッド通ったよ！\n", FILE_APPEND);
        $this->Dto->VcrCreData();                           //DTOにPOSTされた明細行を渡す
        $details = $this->Dto->DtoDetails;                  //DTOから明細行を取得
        $Accounts = $this->Dto->Accounts;
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            requireCsrf();
            $this->Service->VcrCreate($this->Dto);
        }
        $this->Render('Create');
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

        $this->Service->saveVoucher($data);

        header('Location: index.php?route=voucher.index');
        exit;
    }
    // 一覧
    public function index() {
        $userId = getLoginUserId();
        $vouchers = $this->Service->list($userId);
        require ROOT_PATH.'/views/voucher/index.php';
    }

    // 修正、削除データ検索
    public function list() {
        //echo "<pre>"; var_dump($_POST); echo "</pre><br><br><br>";
        $this->Dto->List(); //DTOのListメソッドで検索条件をセット
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            requireCsrf();                              //CSRFトークンの検証
            $this->Dto->List(); //DTOのListメソッドで検索条件をセット 未入力の場合はセッションから検索条件をセットするため、POSTされた検索条件をDTOにセットする前にList()メソッドを呼び出す必要があります。

            if (isset($_POST['SimpleSearch'])) {        //修正データ一覧作成
                $this->Service->VcrSimpleSearch($this->Dto , $this->Repo, $this->Validator);
            }
            if (isset($_POST['VcrUpdateNo'])) {         //修正対象データ　編集用データ作成
                $this->Service->VcrUpdNo($this->Dto, $this->Repo, $this->Validator);
            }
            //前回の行追加、行削除の処理は、修正対象データの編集用データ作成の後に行う必要があり、
            //行追加、行削除の処理は、編集用データを基に行う必要があるためです。
            //もし、行追加、行削除の処理を先に行ってしまうと、
            //編集用データがまだ作成されていない状態で行追加、行削除の処理が行われてしまい、正しく処理できなくなってしまいます。
            //if( isset($_POST['VcrAddDebit'])  || isset($_POST['VcrAddCredit'])  || isset($_POST['VcrDetailLineDel'])) {
            //    $this->Service->VcrSearchedDataRemake($this->Dto , $this->Repo, $this->Validator);
            //}
            if( isset($_POST['VcrAddDebit'])) {         //行追加ボタン（借方）を押したときの処理
                $this->Service->VcrAddDebit($this->Dto, $this->Repo, $this->Validator);
            }
            if( isset($_POST['VcrAddCredit'])) {        //行追加ボタン（貸方）を押したときの処理
                $this->Service->VcrAddCredit($this->Dto, $this->Repo, $this->Validator);
            }
            if( isset($_POST['VcrDetailLineDel'])) {    //仕分け編集データから　一行削除
                $this->Service->VcrDetailLineDel($this->Dto, $this->Repo, $this->Validator);
            }
            if( isset($_POST['VcrDelete'])) {           //1仕分け伝票削除
                $Success = $this->Service->VcrDelete($this->Dto, $this->Repo, $this->Validator);
                if ($Success) {
                    file_put_contents('/var/www/html/test6/public/debug.log', "Success2 = {$Success}！\n", FILE_APPEND);
                    // 3. ユーザーへの完了通知メッセージだけをセッションに仕込む
                    $_SESSION['flash_message'] = "伝票を正常に削除しました。";        
                    // 4. そのまま一覧画面（または新規作成画面）へ一発リダイレクト！
                    header('Location: index.php?route=voucher.list'); //リダイレクトしてPOSTデータの再送信を防止
                    exit;
                }
            }

            if( isset($_POST['VcrUpdate'])) {           //1仕分け伝票DB更新
                $Success = $this->Service->VcrDelete($this->Dto, $this->Repo, $this->Validator);
                if ($Success) {
                //    file_put_contents('/var/www/html/test6/public/debug.log', "Success2 = {$Success}！\n", FILE_APPEND);
                //    unset($_SESSION['VcrListResult']); //セッションの検索結果をクリア
                //    unset($_SESSION['VcrSearchedData']); //セッションの修正用デ
                //    // 3. ユーザーへの完了通知メッセージだけをセッションに仕込む
                //    $_SESSION['flash_message'] = "伝票を正常に削除しました。";
        
                //    // 4. そのまま一覧画面（または新規作成画面）へ一発リダイレクト！
                    header('Location: index.php?route=voucher.list'); //リダイレクトしてPOSTデータの再送信を防止
                    exit;
            }
        }
        $this->Render('List');
        //require ROOT_PATH.'/views/voucher/list.php';

    }

    // 編集画面
    public function edit(){
        $id = (int)($_GET['id'] ?? 0);
        $voucher = $this->Service->find($id);
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
        $this->Service->update($id, $data);
        header('Location: index.php?route=voucher.index');
        exit;
    }
    private function Render($RenderType): int{
        $VcrListResult = $Dto->VcrListResult ?? [];
        $Accounts = $this->Dto->Accounts ?? [];
        if($RenderType === 'Create'){
            $TokenKey  = generateCsrfToken();
            require ROOT_PATH . '/views/voucher/create.php';
            return 1;
        }
        if($RenderType === 'List'){
            //if($_SESSION['VcrListResult['])
            //echo "<pre>"; var_dump($_SESSION['VcrSearchedData']??[]);  echo "</pre><br><br><br>";
            $TokenKey  = generateCsrfToken();
            require ROOT_PATH.'/views/voucher/list.php';
            return 1;
        }
    }

    // 削除
//    public function delete(){
//        $id = (int)($_GET['id'] ?? 0);
//        $this->Service->delete($id);
//        header('Location: index.php?route=voucher.index');
//        exit;
//    }
//削除はlist()の中で行うため、delete()メソッドは不要になりました。
}