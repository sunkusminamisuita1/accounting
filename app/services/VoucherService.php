<?php
require_once ROOT_PATH . '/app/repositories/voucherRepository.php';

class VoucherService{
    private VoucherRepository $repo;
    public function __construct()    {
        $this->repo = new VoucherRepository();
    }

    public function list(int $userId): array {
        return $this->repo->findAllByUser($userId);
    }

    public function find(int $id) {
        return $this->repo->find($id);
    }

    public function update(int $id, array $data){
        $this->repo->update($id, $data);
    }

    public function delete(int $id) {
        $this->repo->delete($id);
    }

    public function InitializeSession(): void    {
        $_SESSION['voucherRows'] = $_SESSION['voucherRows'] ?? [];
        $_SESSION['slipNum'] = $_SESSION['slipNum'] ?? 0;
        $_SESSION['editData'] = $_SESSION['editData'] ?? [];
        $_SESSION['debitAmountTotal'] = $_SESSION['debitAmountTotal'] ?? 0;
        $_SESSION['creditAmountTotal'] = $_SESSION['creditAmountTotal'] ?? 0;
    }

    public function getAccounts(): array {
        return $this->repo->getAccounts();
    }

    public function getVoucherRows(): array {
        return $_SESSION['voucherRows'] ?? [];
    }

    public function getEditData(): array{
        return $_SESSION['editData'] ?? [];
    }

    public function getTotals(): array{
        return [
            'debitAmountTotal' => $_SESSION['debitAmountTotal'] ?? 0,
            'creditAmountTotal' => $_SESSION['creditAmountTotal'] ?? 0,
        ];
    }

    public function addEntry($VoucherDto): void {
        $this->initializeSession();
        $voucherDate = $VoucherDto->Date;
        $side = $VoucherDto->side ?? '';
        $accountId = isset($VoucherDto->accountId) ? (int)$VoucherDto->accountId : 9999;
        $amount = $VoucherDto->amount ?? 0;
        $summary = $VoucherDto->Summary ?? '';
        $accountName = $this->resolveAccountName($accountId);
        if ($accountId !== 9999 && $accountId > 0) {
            $_SESSION['voucherRows'][$_SESSION['slipNum']] = [
                'date' => $voucherDate,
                'side' => $side,
                'accountId' => $accountId,
                'accountName' => $accountName,
                'amount' => $amount,
                'summary' => $summary,
            ];
            $_SESSION['slipNum']++;
        }
        $this->recalculateTotals();
    }

    public function deleteRows(array $keys): void {
        foreach ($keys as $key) {
            unset($_SESSION['voucherRows'][(int)$key]);
        }
        $this->recalculateTotals();
    }

    public function setEditRow(int $key): void{
        if (isset($_SESSION['voucherRows'][$key])) {
            $_SESSION['editData'] = $_SESSION['voucherRows'][$key];
            unset($_SESSION['voucherRows'][$key]);
            $this->recalculateTotals();
        }
    }

    public function clearEntries(): void {
        unset($_SESSION['voucherRows']);
        unset($_SESSION['slipNum']);
        unset($_SESSION['editData']);
        unset($_SESSION['debitAmountTotal']);
        unset($_SESSION['creditAmountTotal']);
    }

    public function saveVoucher(array $VoucherDto): void{

        $IndexCnt = count($VoucherDto->AccountId) ?? 0;
        $this->repo->insertVoucher($voucherDto); 











    //    $rows = $this->getVoucherRows();
    //    $this->recalculateTotals();
    //    $debitTotal = $_SESSION['debitAmountTotal'] ?? 0;
    //    $creditTotal = $_SESSION['creditAmountTotal'] ?? 0;
    //    if ($debitTotal !== $creditTotal) {
    //        throw new Exception('借方と貸方の合計が一致しません');
    //    }
    //    if (empty($rows)) {
    //        throw new Exception('伝票明細がありません');
    //    }
    //    $voucherData = [
    //        'voucher_date' => $data['voucher_date'] ?? $rows[array_key_first($rows)]['date'],
    //        'summary' => $data['summary'] ?? '',
    //    ];
    //    $debits = $this->buildDetails($rows, '借方');
    //    $credits = $this->buildDetails($rows, '貸方');
    //    $this->repo->insertVoucher($voucherData, $debits, $credits);
    //    $this->clearEntries();
    }

    private function resolveAccountName(int $accountId): string {
        foreach ($this->getAccounts() as $account) {
            if ($account['id'] === $accountId) {
                return $account['name'];
            }
        }
        return '';
    }

    private function buildDetails(array $rows, string $side): array{
        $items = [];
        foreach ($rows as $row) {
            if ($row['side'] === $side) {
                $items[] = [
                    'account_id' => $row['accountId'],
                    'amount' => (int)$row['amount'],
                    'side' => $side === '借方' ? 'debit' : 'credit',
                ];
            }
        }
        return $items;
    }

    private function recalculateTotals(): void{
        $debit = 0;
        $credit = 0;
        foreach ($_SESSION['voucherRows'] ?? [] as $row) {
            if ($row['side'] === '貸方') {
                $credit += (int)$row['amount'];
            } else {
                $debit += (int)$row['amount'];
            }
        }
        $_SESSION['debitAmountTotal'] = $debit;
        $_SESSION['creditAmountTotal'] = $credit;
    }
}
