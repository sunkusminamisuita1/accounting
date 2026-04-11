<?php
class VoucherService {
    private $repo;

    public function __construct() {
        $this->repo = new VoucherRepository();
    }

    // VAddService の機能
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

    // VCreateService の機能
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

    // VDeleteService の機能
    public function executeDelete(VDeleteDTO $dto): void {
        if ($dto->action === 'clear') {
            // 全削除処理
            unset($_SESSION['voucherRows']);
            $_SESSION['slipNum'] = 0;
            $_SESSION['flash_message'] = "入力内容をすべて削除しました。";
            $_SESSION['creditAmountTotal'] = 0;
            $_SESSION['debitAmountTotal'] = 0;
        } elseif ($dto->action === 'alt') {
            // 個別削除処理
            if (!empty($dto->deleteKeys)) {
                foreach ($dto->deleteKeys as $key) {
                    unset($_SESSION['voucherRows'][$key]);
                }
            }

            // 修正処理（選択した1件を入力欄に戻す）
            if ($dto->updateKey !== null) {
                if (isset($_SESSION['voucherRows'][$dto->updateKey])) {
                    $target = $_SESSION['voucherRows'][$dto->updateKey];
                    $_SESSION['edit_data'] = $target;
                    unset($_SESSION['voucherRows'][$dto->updateKey]);
                } else {
                    throw new Exception('修正対象のデータが見つかりません');
                }
            }

            // 合計を再計算
            $this->recalculateTotals();
            $_SESSION['flash_message'] = "明細を更新しました。";
        }
    }

    // 共通のプライベートメソッド
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