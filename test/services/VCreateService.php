<?php
class VCreateService {
    private $repo;

    public function __construct() {
        $this->repo = new VoucherRepository();
    }

    public function initializeVoucher(): VCreateDTO {
        // セッション初期化
        if (!isset($_SESSION['voucherRows'])) {
            $_SESSION['voucherRows'] = [];
        }
        if (!isset($_SESSION['slipNum'])) {
            $_SESSION['slipNum'] = 0;
        }
        if (!isset($_SESSION['debitAmountTotal'])) {
            $_SESSION['debitAmountTotal'] = 0;
        }
        if (!isset($_SESSION['creditAmountTotal'])) {
            $_SESSION['creditAmountTotal'] = 0;
        }

        // バランスチェック
        $diff = $_SESSION['debitAmountTotal'] - $_SESSION['creditAmountTotal'];
        $isBalanced = ($diff === 0 && !empty($_SESSION['voucherRows']));

        return new VCreateDTO(
            $_SESSION['voucherRows'],
            $_SESSION['debitAmountTotal'],
            $_SESSION['creditAmountTotal'],
            $isBalanced
        );
    }

    public function getAccounts(): array {
        $pdo = getPDO();
        $accounts = $pdo->query("SELECT id, name FROM accounts ORDER BY id")->fetchAll();
        array_unshift($accounts, ['id' => 9999, 'name' => '----------']);
        return $accounts;
    }

    public function recalculateTotals(): void {
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