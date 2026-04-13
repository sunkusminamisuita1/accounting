<?php
class VoucherRepository{

    public function findAllByUser(int $userId): array {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT id, voucher_date, summary
            FROM journal_vouchers
            WHERE user_id = ?
            ORDER BY voucher_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            SELECT *
            FROM journal_vouchers
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            UPDATE journal_vouchers
            SET voucher_date = ?, summary = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['date'],
            $data['summary'],
            $id
        ]);
    }

    public function delete(int $id) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("
            DELETE FROM journal_vouchers
            WHERE id = ?
        ");
        $stmt->execute([$id]);
    }

    public function getAccounts()  {
        $pdo = getPDO();
        $stmt = $pdo->query("
            SELECT id, name, type
            FROM accounts
            ORDER BY id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insertVoucher($data,$debits,$credits){
        $pdo = getPDO();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
            INSERT INTO journal_vouchers
            (voucher_date, summary, user_id)
            VALUES (?,?,?)
            ");
            $stmt->execute([
                $data['voucher_date'],
                $data['summary'],
                $_SESSION['user']['id']
            ]);
            $voucherId = $pdo->lastInsertId();
            $stmtDetail = $pdo->prepare("
                INSERT INTO journal_details
                    (voucher_id, account_id, side, amount)
                    VALUES (?,?,?,?)
            ");
            foreach ($debits as $d) {
                $stmtDetail->execute([
                    $voucherId,
                    $d['account_id'],
                    'debit',
                    $d['amount']
                ]);
            }
            foreach ($credits as $c) {
                $stmtDetail->execute([
                    $voucherId,
                    $c['account_id'],
                    'credit',
                    $c['amount']
                ]);
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
