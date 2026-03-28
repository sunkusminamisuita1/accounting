<?php
class ReportRepository{
    public function getJournalSummary($from,$to,$userId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare(
                    "SELECT
                        a.id as account_id,
                        a.name,
                        a.type,
                        jd.side,
                            SUM(jd.amount) AS total
                    FROM journal_details jd
                    JOIN journal_vouchers jv
                        ON jd.voucher_id = jv.id
                    JOIN accounts a
                        ON jd.account_id = a.id
                    WHERE jv.voucher_date BETWEEN :from AND :to
                        AND jv.user_id = :user_id
                    GROUP BY a.id,a.name,a.type,jd.side
                    ORDER BY a.id"
        );
        $stmt->execute([
            ':from'=>$from,
            ':to'=>$to,
            ':user_id'=>$userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
