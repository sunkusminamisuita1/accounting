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
    
    public function insertVoucher($Dto){
        $IndexCount = count($Dto->account_id);
        $pdo = getPDO();
        $pdo->beginTransaction();
        try {
            
            $stmt = $pdo->prepare("
                INSERT INTO journal_vouchers
                    (voucher_date, summary, user_id, created_at)
                    VALUES (?,?,?,?)
            ");
            $stmt->execute([
                $Dto->Date,
                $Dto->Summary  ,
                $_SESSION['user']['id'],
                date('Y-m-d H:i:s')
            ]);
            
            $stmtDetail = $pdo->prepare("
                INSERT INTO journal_details
                    (voucher_id, account_id, side, amount)
                    VALUES (?,?,?,?)
            ");
            $voucherId = $pdo->lastInsertId();

            for ($i = 0; $i < $IndexCount; $i++) {
                if($Dto->side === 'debit') {
                    $stmtDetail->execute([
                        $voucherId,
                        $Dto->account_id[$i],
                        'debit',
                        $Dto->amount[$i]
                    ]);
                } else {
                     $stmtDetail->execute([
                        $voucherId,
                        $Dto->account_id[$i],
                        'credit',
                        $Dto->amount[$i]
                    ]);
                }
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
