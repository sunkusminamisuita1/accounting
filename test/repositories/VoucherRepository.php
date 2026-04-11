<?php
class VoucherRepository {
    // VAddRepository の機能
    public function insertJournalDetail(int $voucherId, VAddDTO $dto, int $userId): int {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            INSERT INTO journal_details (voucher_id, account_id, side, amount)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $voucherId,
            (int)$dto->accountId,
            $dto->side === '借方' ? 'debit' : 'credit',
            (int)$dto->amount
        ]);

        return (int)$pdo->lastInsertId();
    }

    public function getVoucher(int $voucherId, int $userId): ?array {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT id, voucher_date, summary, user_id
            FROM journal_vouchers
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([$voucherId, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function getJournalDetails(int $voucherId): array {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT jd.id, jd.voucher_id, jd.account_id, jd.side, jd.amount,
                   a.name as account_name
            FROM journal_details jd
            JOIN accounts a ON jd.account_id = a.id
            WHERE jd.voucher_id = ?
            ORDER BY jd.id
        ");

        $stmt->execute([$voucherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VCreateRepository の機能
    public function insertVoucher(string $voucherDate, string $summary, int $userId): int {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            INSERT INTO journal_vouchers (voucher_date, summary, user_id)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$voucherDate, !empty($summary) ? $summary : null, $userId]);

        return (int)$pdo->lastInsertId();
    }

    public function insertBulkJournalDetails(int $voucherId, array $voucherRows): int {
        $pdo = getPDO();
        $insertedCount = 0;

        foreach ($voucherRows as $row) {
            $stmt = $pdo->prepare("
                INSERT INTO journal_details (voucher_id, account_id, side, amount)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $voucherId,
                (int)$row['accountId'],
                $row['side'] === '借方' ? 'debit' : 'credit',
                (int)$row['amount']
            ]);

            $insertedCount++;
        }

        return $insertedCount;
    }

    public function getVouchersByUser(int $userId, int $limit = 10, int $offset = 0): array {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT id, voucher_date, summary, user_id
            FROM journal_vouchers
            WHERE user_id = ?
            ORDER BY voucher_date DESC
            LIMIT ? OFFSET ?
        ");

        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countVouchersByUser(int $userId): int {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM journal_vouchers
            WHERE user_id = ?
        ");

        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['count'];
    }

    // VDeleteRepository の機能
    public function deleteJournalDetails(array $detailIds, int $userId): int {
        $pdo = getPDO();
        $deletedCount = 0;

        foreach ($detailIds as $detailId) {
            // ユーザーの伝票に属していることを確認
            $stmt = $pdo->prepare("
                DELETE FROM journal_details
                WHERE id = ? AND voucher_id IN (
                    SELECT id FROM journal_vouchers WHERE user_id = ?
                )
            ");

            $stmt->execute([$detailId, $userId]);
            $deletedCount += $stmt->rowCount();
        }

        return $deletedCount;
    }

    public function deleteVoucher(int $voucherId, int $userId): bool {
        $pdo = getPDO();

        // 伝票がユーザーのものか確認
        $stmt = $pdo->prepare("
            SELECT id FROM journal_vouchers WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$voucherId, $userId]);

        if (!$stmt->fetch()) {
            return false;
        }

        // 明細を削除
        $stmt = $pdo->prepare("DELETE FROM journal_details WHERE voucher_id = ?");
        $stmt->execute([$voucherId]);

        // 伝票を削除
        $stmt = $pdo->prepare("DELETE FROM journal_vouchers WHERE id = ?");
        $stmt->execute([$voucherId]);

        return true;
    }

    public function getJournalDetail(int $detailId, int $userId): ?array {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT jd.id, jd.voucher_id, jd.account_id, jd.side, jd.amount,
                   a.name as account_name
            FROM journal_details jd
            JOIN accounts a ON jd.account_id = a.id
            JOIN journal_vouchers jv ON jd.voucher_id = jv.id
            WHERE jd.id = ? AND jv.user_id = ?
        ");

        $stmt->execute([$detailId, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}
?>