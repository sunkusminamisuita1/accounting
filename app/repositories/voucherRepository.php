<?php
require_once ROOT_PATH . '/app/services/voucherService.php';
require_once ROOT_PATH . '/app/dto/voucherDto.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/validators/voucherValidator.php';

class voucherRepository{
    
    private voucherService $service;
    private voucherDto $dto;
    private voucherValidator $validator;
    //private errMsgPopUp $errMsgPopUp;
    private string $renderType;

    public function __construct()  {

    }

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
        try{
            $pdo = getPDO();
            $pdo->beginTransaction();

            // 伝票に紐づく明細を削除
             $stmtDetails = $pdo->prepare("DELETE FROM journal_details WHERE voucher_id = ?");
             $stmtDetails->execute([$id]);

            // 伝票を削除
            $stmtVoucher = $pdo->prepare("DELETE FROM journal_vouchers WHERE id = ?");
            $stmtVoucher->execute([$id]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        if ($stmtVoucher->rowCount() > $stmtDetails->rowCount()) {
            return $stmtVoucher->rowCount();
        } else {
            return $stmtDetails->rowCount();
        }
    }

    public function jvJdDelete($dto) {
            $voucherId  =   $dto->vcrSearchedData[0]['voucher_id'];
        try{
            $pdo = getPDO();
            $pdo->beginTransaction();

            // 伝票に紐づく明細を削除
            $stmtDetails = $pdo->prepare("DELETE FROM journal_details WHERE voucher_id = ?");
            $stmtDetails->execute([$voucherId]);

            // 伝票を削除
            $stmtVoucher = $pdo->prepare("DELETE FROM journal_vouchers WHERE id = ?");
            $stmtVoucher->execute([$voucherId]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        if ($stmtVoucher->rowCount() > $stmtDetails->rowCount()) {
            return $stmtVoucher->rowCount();
        } else {
            return $stmtDetails->rowCount();
        }
    }

    public function getAccounts()  {
        try{
            $pdo = getPDO();
            $stmt = $pdo->query("
                SELECT id, name, type
                FROM accounts
                ORDER BY id
            ");
        } catch (Exception $e){
            throw $e;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insertVoucher($dto){
        $indexCount = count($dto->dtoDetails);
        $pdo = getPDO();
        $pdo->beginTransaction();

        if( isset($_POST['vcrUpdate'])) {
            $voucherId  =   (int)$dto->vcrSearchedData[0]['id'];
        }

        try {
            
            $stmt = $pdo->prepare("
                INSERT INTO journal_vouchers
                    (voucher_date, summary, user_id, shop_code created_at)
                    VALUES (?,?,?,?,?)
            ");
            $stmt->execute([
                $dto->date,
                $dto->summary  ,
                $_SESSION['user']['id'],
                $_SESSION['current_shop_code'] ?? '',
                date('Y-m-d H:i:s')
            ]);

            $voucherId = (int)$pdo->lastInsertId();
           
            $stmtDetail = $pdo->prepare("
                INSERT INTO journal_details
                    (voucher_id, jd_summary, account_id, side, amount)
                    VALUES (?,?,?,?,?)
            ");

            foreach ($dto->dtoDetails as $recNo => $row){
                if($row['side'] === 'debit') {
                    $stmtDetail->execute([
                        $voucherId,
                        $row['jd_summary'] ?? "" ,
                        $row['account_id'],
                        'debit',
                        $row['amount']
                    ]);
                } else {
                     $stmtDetail->execute([
                        $voucherId,
                        $row['jd_summary'],
                        $row['account_id'],
                        'credit',
                        $row['amount']
                    ]);
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function vcrListSearch($vcrDto) {

        if(!empty($vcrDto->date)){
            $from = date('Y-m-d', strtotime($vcrDto->date));
            $to =   date('Y-m-d', strtotime($vcrDto->date));
        }
        if(
            !empty($vcrDto->vcrListDatePeriod['検索開始日付'] )   &&
            !empty($vcrDto->vcrListDatePeriod['検索終了日付'] )
        )
        {
            $from = date('Y-m-d', strtotime($vcrDto->vcrListDatePeriod['検索開始日付']));
            $to   =  date('Y-m-d', strtotime($vcrDto->vcrListDatePeriod['検索終了日付']));
        }

        if(empty($from) || empty($to)) {
            $from   =   '1970-01-01';
            $to     =   '2099-12-31';
        }

        $userId = getLoginUserId();
        $pdo = getPDO();

         $sql = "SELECT 
                jv.id,
                jd.id as JdId,
                jv.voucher_date,
                jv.summary,
                a.id as account_id,
                a.name,
                a.type,
                jd.jd_summary as jd_summary,
                jd.side,
                jd.amount,
                jd.voucher_id
            FROM journal_vouchers jv
            JOIN journal_details jd ON jv.id            = jd.voucher_id
            JOIN accounts a         ON jd.account_id    = a.id
            WHERE jv.user_id = :user_id
              AND jv.voucher_date BETWEEN :from AND :to";             

        // 条件がある場合だけ絞り込むロジック
        if (!empty($vcrDto->listVcrNum)) {
            $sql .= " AND jv.id = :vchrnumber ";
        }
        if (!empty($vcrDto->summary)) {
            $sql .= " AND jv.summary LIKE :vchrsummary ";
        }
        $sql .= " GROUP BY jd.voucher_id,jd.id";

        $stmt = $pdo->prepare($sql);    
        $params = [
            ':from'   => $from,
            ':to'     => $to,
            ':user_id' => $userId
        ];
        if (!empty($vcrDto->listVcrNum)) $params[':vchrnumber'] = $vcrDto->listVcrNum;
        if (!empty($vcrDto->summary))   $params[':vchrsummary'] = '%' . $vcrDto->summary . '%';
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

