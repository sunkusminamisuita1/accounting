<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';

class VoucherController
{
    private VoucherService $service;

    public function __construct()
    {
        $this->service = new VoucherService();
    }

    public function create(): void
    {
        requireLogin();
        $this->service->initializeSession();
        $accounts = $this->service->getAccounts();
        $voucherRows = $this->service->getVoucherRows();
        $editData = $this->service->getEditData();
        $totals = $this->service->getTotals();
        $isBalanced = $totals['debitAmountTotal'] === $totals['creditAmountTotal']
            && !empty($voucherRows);
        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        require ROOT_PATH . '/views/voucher/create.php';
    }

    public function add(): void
    {
        requireLogin();
        requirePost();
        verifyCsrfToken($_POST['csrfToken'] ?? '');

        if (isset($_POST['clear'])) {
            $this->service->clearEntries();
            $_SESSION['flash_message'] = '入力内容をすべて削除しました。';
        } elseif (isset($_POST['alt'])) {
            if (!empty($_POST['deleteKeys']) && is_array($_POST['deleteKeys'])) {
                $this->service->deleteRows($_POST['deleteKeys']);
            }
            if (isset($_POST['update_key'])) {
                $this->service->setEditRow((int)$_POST['update_key']);
            }
        } elseif (isset($_POST['add'])) {
            $this->service->addEntry($_POST);
        }

        header('Location: index.php?route=voucher.create');
        exit;
    }

    public function store(): void
    {
        requireLogin();
        requirePost();
        verifyCsrfToken($_POST['csrfToken'] ?? '');

        $this->service->saveVoucher($_POST);
        $_SESSION['flash_message'] = '伝票を登録しました。';

        header('Location: index.php?route=home');
        exit;
    }
}
