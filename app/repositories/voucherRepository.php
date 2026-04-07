<?php
class VoucherRepository{
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
