<?php
<?php
class VoucherValidator {
    public function VAddValidate(VoucherDTO $dto): array {
        $errors = [];

        // voucherDateの検証
        if (empty($dto->voucherDate)) {
            $errors['voucherDate'] = '伝票日付は必須です';
        } else if (!$this->isValidDate($dto->voucherDate)) {
            $errors['voucherDate'] = '伝票日付の形式が不正です（YYYY-MM-DD形式で入力してください）';
        }

        // sideの検証
        if (empty($dto->side)) {
            $errors['side'] = '借方/貸方は必須です';
        } else if (!in_array($dto->side, ['借方', '貸方'])) {
            $errors['side'] = '借方/貸方の値が不正です';
        }

        // accountIdの検証
        if (empty($dto->accountId)) {
            $errors['accountId'] = '勘定科目は必須です';
        } else if ((int)$dto->accountId === 9999) {
            $errors['accountId'] = '勘定科目を選択してください';
        } else if ((int)$dto->accountId <= 0) {
            $errors['accountId'] = '勘定科目の値が不正です';
        } else {
            // DBで勘定科目が存在するか確認
            if (!$this->accountExists((int)$dto->accountId)) {
                $errors['accountId'] = '指定された勘定科目が見つかりません';
            }
        }

        // amountの検証
        if (empty($dto->amount)) {
            $errors['amount'] = '金額は必須です';
        } else if (!is_numeric($dto->amount) || (int)$dto->amount <= 0) {
            $errors['amount'] = '金額は正の整数で入力してください';
        }

        // summaryの検証（オプション項目）
        if (!empty($dto->summary) && strlen($dto->summary) > 255) {
            $errors['summary'] = '摘要は255文字以内で入力してください';
        }

        return $errors;
    }

    private function isValidDate(string $date): bool {
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($pattern, $date)) {
            return false;
        }
        list($year, $month, $day) = explode('-', $date);
        return checkdate((int)$month, (int)$day, (int)$year);
    }

    private function accountExists(int $accountId): bool {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id FROM accounts WHERE id = ?");
        $stmt->execute([$accountId]);
        return $stmt->fetch() !== false;
    }

    public function VCreateValidate(VoucherCreateDTO $dto): array {
        $errors = [];

        // voucherRowsの検証
        if (empty($dto->voucherRows)) {
            $errors['voucherRows'] = '最低1件の明細が必要です';
        } else {
            // 各行の検証
            foreach ($dto->voucherRows as $index => $row) {
                if (empty($row['date'])) {
                    $errors["voucherRows_$index"] = "行$index: 伝票日付が必須です";
                }
                if (empty($row['side'])) {
                    $errors["voucherRows_$index"] = "行$index: 借方/貸方が必須です";
                }
                if (empty($row['accountId'])) {
                    $errors["voucherRows_$index"] = "行$index: 勘定科目が必須です";
                }
                if (empty($row['amount']) || (int)$row['amount'] <= 0) {
                    $errors["voucherRows_$index"] = "行$index: 有効な金額が必須です";
                }
            }
        }

        // 合計の検証
        if ($dto->debitAmountTotal != $dto->creditAmountTotal) {
            $errors['balance'] = '借方合計と貸方合計が一致していません（借方: ' . $dto->debitAmountTotal . '、貸方: ' . $dto->creditAmountTotal . '）';
        }

        // バランスフラグの検証
        if (!$dto->isBalanced) {
            $errors['isBalanced'] = '伝票がバランスしていません。借方と貸方が一致してください。';
        }

        return $errors;
    }

    public function VDeletevalidate(VoucherDeleteDTO $dto): array {
        $errors = [];

        // actionの検証
        if (empty($dto->action)) {
            $errors['action'] = 'アクションは必須です';
        } else if (!in_array($dto->action, ['clear', 'alt'])) {
            $errors['action'] = 'アクションが不正です';
        }

        // clearアクションの場合の検証
        if ($dto->action === 'clear') {
            if (empty($_SESSION['voucherRows'])) {
                $errors['voucherRows'] = '削除する伝票明細がありません';
            }
        }

        // altアクションの場合の検証
        if ($dto->action === 'alt') {
            $hasDeleteKeys = !empty($dto->deleteKeys) && is_array($dto->deleteKeys);
            $hasUpdateKey = $dto->updateKey !== null;

            if (!$hasDeleteKeys && !$hasUpdateKey) {
                $errors['altAction'] = '削除または修正対象を選択してください';
            }

            // deleteKeysの検証
            if ($hasDeleteKeys) {
                foreach ($dto->deleteKeys as $key) {
                    if (!isset($_SESSION['voucherRows'][$key])) {
                        $errors["deleteKey_$key"] = "削除対象の行が見つかりません（キー: $key）";
                    }
                }
            }

            // updateKeyの検証
            if ($hasUpdateKey && !isset($_SESSION['voucherRows'][$dto->updateKey])) {
                $errors['updateKey'] = "修正対象の行が見つかりません（キー: {$dto->updateKey}）";
            }
        }

        return $errors;
    }

}
// app/validators/VoucherValidator.php
//class VoucherValidator
//{
//    public function validate(VoucherDTO $dto): void
//    {
//        if (empty($dto->date)) {
//            throw new Exception('日付は必須です');
//        }
//        if (empty($dto->details)) {
//            throw new Exception('明細がありません');
//        }
//        $debit = 0;
//        $credit = 0;
//        foreach ($dto->details as $d) {
//            if ($d->amount <= 0) {
//                throw new Exception('金額は0より大きくしてください');
//            }
//            if (!in_array($d->side, ['debit', 'credit'])) {
//                throw new Exception('貸借区分が不正です');
//            }
//            if ($d->side === 'debit') {
//                $debit += $d->amount;
//            } else {
//                $credit += $d->amount;
//            }
//        }
//        if ($debit !== $credit) {
//            throw new Exception('借方と貸方が一致しません');
//        }
//    }
//}
?>