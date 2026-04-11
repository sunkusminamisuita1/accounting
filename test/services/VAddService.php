<?php
class VAddService {
    private $repo;

    public function __construct() {
        $this->repo = new VoucherRepository();
    }

    public function addVoucherRow(VAddDTO $dto): void {
        if (!isset($_SESSION['voucherRows'])) {
            $_SESSION['voucherRows'] = [];
        }
        if (!isset($_SESSION['slipNum'])) {
            $_SESSION['slipNum'] = 0;
        }

        // accountIdの妥当性チェック
        if ((int)$dto->accountId === 9999) {
            throw new Exception('勘定科目を選択してください');
        }

        // 勘定科目名を取得
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT name FROM accounts WHERE id = ?");
        $stmt->execute([(int)$dto->accountId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account) {
            throw new Exception('無効な勘定科目です');
        }

        $slipNum = $_SESSION['slipNum'];
        $_SESSION['voucherRows'][$slipNum] = [
            'date' => $dto->voucherDate,
            'side' => $dto->side,
            'accountId' => (int)$dto->accountId,
            'accountName' => $account['name'],
            'amount' => (int)$dto->amount,
            'summary' => $dto->summary
        ];

        $_SESSION['slipNum']++;

        // 合計を再計算
        $this->recalculateTotals();
    }

    private function recalculateTotals(): void {
        $_SESSION['creditAmountTotal'] = 0;
        $_SESSION['debitAmountTotal'] = 0;

        foreach ($_SESSION['voucherRows'] as $row) {
            if ($row['side'] === '貸方') {
                $_SESSION['creditAmountTotal'] += (int)$row['amount'];
            } else {
                $_SESSION['debitAmountTotal'] += (int)$row['amount'];
            }
        }
    }
}
?>