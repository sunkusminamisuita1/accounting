<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';

class VoucherController
{
    private VoucherService $service;

    public function __construct()  {
        $this->service = new VoucherService();
    }

    public function create(): void    {
        $TokenKey  = generateCsrfToken();
        $details = [];
        for ($i = 0; $i < 5; $i++) {
            $details[$i] = [
                'account_id' => '',
                'amount' => '',
                'side' => ''
            ];
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
        //    var_dump($_POST);
              if (isset($_POST['add_row'])) {
                  $this->service->addEntry($_POST);
                  header('Location: index.php?route=voucher.store');
                  exit;
              }
        }

         $this->service->InitializeSession();

        //accountsテーブルから勘定科目を取得
        $accounts = $this->service->getAccounts();

        //セッションから伝票行、編集対象データ、合計金額を取得(初回は初期化データが入る)
        $voucherRows = $this->service->getVoucherRows();

        //$_SESSION['editData'] を代入　初回は初期化データが入る
        $editData = $this->service->getEditData();

        //借り方合計、貸し方合計を取得　初回は0が入る
        $totals = $this->service->getTotals();

        //借方合計と貸方合計が等しいかつ伝票行が空でない場合はバランスしているとみなす(真偽値が$isBalancedに入る)
        $isBalanced = 
            $totals['debitAmountTotal'] === $totals['creditAmountTotal'] && !empty($voucherRows);
        $flashMessage = $_SESSION['flash_message'] ?? null;

        unset($_SESSION['flash_message']);
        require ROOT_PATH . '/views/voucher/create.php';
    }

    //public function add(): void {
    //    requirePost();
    //    verifyCsrfToken($_POST['csrfToken'] ?? '');
    //    if (isset($_POST['clear'])) {
    //        $this->service->clearEntries();
    //        $_SESSION['flash_message'] = '入力内容をすべて削除しました。';
    //    } elseif (isset($_POST['alt'])) {
    //        if (!empty($_POST['deleteKeys']) && is_array($_POST['deleteKeys'])) {
    //            $this->service->deleteRows($_POST['deleteKeys']);
    //        }
    //        if (isset($_POST['update_key'])) {
    //            $this->service->setEditRow((int)$_POST['update_key']);
    //        }
    //    } elseif (isset($_POST['add'])) {
    //        $this->service->addEntry($_POST);
    //    }
    //    header('Location: index.php?route=voucher.create');
    //    exit;
    

    public function store(): void {
    //    requirePost();
    //    verifyCsrfToken($_POST['csrfToken'] ?? '');
    //    $this->service->saveVoucher($_POST);
    //    $_SESSION['flash_message'] = '伝票を登録しました。';
    //    header('Location: index.php?route=home');
    //    exit;

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

        $this->service->saveVoucher($data);
//        var_dump($_POST);
//        exit;
        // ここでDTOに変換（次のステップ）
//        $this->service->saveFromPost($data);

        header('Location: index.php?route=voucher.index');
        exit;
    }

        // 一覧
    public function index() {
        $userId = getLoginUserId();
        $vouchers = $this->service->list($userId);
        print_r($vouchers);
        require ROOT_PATH.'/views/voucher/index.php';
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
        header('Location: index.php?route=voucher.index');
        exit;
    }

    // 削除
    public function delete(){
        $id = (int)($_GET['id'] ?? 0);
        $this->service->delete($id);
        header('Location: index.php?route=voucher.index');
        exit;
    }
}
