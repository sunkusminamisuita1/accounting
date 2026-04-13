<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';

class VoucherController
{
    private VoucherService $service;

    public function __construct()  {
        $this->service = new VoucherService();
    }

    public function create(): void    {
        $this->service->InitializeSession();
        $accounts = $this->service->getAccounts();
        $voucherRows = $this->service->getVoucherRows();
        $editData = $this->service->getEditData();
        $totals = $this->service->getTotals();
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
    //}

    public function store(): void {
    //    requirePost();
    //    verifyCsrfToken($_POST['csrfToken'] ?? '');
    //    $this->service->saveVoucher($_POST);
    //    $_SESSION['flash_message'] = '伝票を登録しました。';
    //    header('Location: index.php?route=home');
    //    exit;

        requirePost();
        $data = $_POST;
        var_dump($_POST);
        exit;
        // ここでDTOに変換（次のステップ）
        $this->service->saveFromPost($data);

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
