<?php
echo "<br>vc1";
require_once ROOT_PATH.'/app/services/voucherService.php';
echo "-vc2";
class VoucherController{

    private $service;

    public function __construct() {
        $this->service = new VoucherService();
    }
    public function create() {
        requireLogin();
        $accounts = $this->service->getAccounts();
        $debits  = $_SESSION['voucherDebit'] ?? [];
        $credits = $_SESSION['voucherCredit'] ?? [];
        require ROOT_PATH.'/views/voucher/create.php';
    }
    public function add() {
        requireLogin();
        requirePost();
        verifyCsrfToken($_POST['csrfToken'] ?? '');
        $this->service->addEntry($_POST);
        header('Location: index.php?route=voucher.create');
        exit;
    }
    public function store() {
        requireLogin();
        requirePost();
        verifyCsrfToken($_POST['csrfToken'] ?? '');
        $this->service->saveVoucher($_POST);
        unset($_SESSION['voucherDebit']);
        unset($_SESSION['voucherCredit']);
        header('Location: index.php?route=home');
        exit;
    }
}
