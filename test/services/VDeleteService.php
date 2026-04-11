<?php
class VDeleteService {
    private $repo;

    public function __construct() {
        $this->repo = new VoucherRepository();
    }

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