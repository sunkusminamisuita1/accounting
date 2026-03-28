<?php
echo "<br>voucherservice.php";
require_once ROOT_PATH.'/app/repositories/voucherRepository.php';
class VoucherService{
    private $repo;
    public function __construct() {
        $this->repo = new VoucherRepository();
    }
//reportrepository.php getaccountと重複のためコメントアウト
//    public function getAccounts() {
//        return $this->repo->getAccounts();
//    }
    public function addEntry($data) {
        $side = $data['side'];
        $entry = [
            'account_id' => $data['account_id'],
            'amount' => (int)$data['amount']
        ];
        if ($side === 'debit') {
            $_SESSION['voucherDebit'][] = $entry;
        } else {
            $_SESSION['voucherCredit'][] = $entry;
        }
    }
    public function saveVoucher($data) {
        $debits  = $_SESSION['voucherDebit'] ?? [];
        $credits = $_SESSION['voucherCredit'] ?? [];
        $debitTotal  = array_sum(array_column($debits,'amount'));
        $creditTotal = array_sum(array_column($credits,'amount'));
        if ($debitTotal !== $creditTotal) {
            throw new Exception("借方と貸方が一致しません");
        }
        $this->repo->insertVoucher($data,$debits,$credits);
    }
}
