<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';
require_once ROOT_PATH . '/app/DTO/VoucherDTO.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/Validators/VoucherValidator.php';

class VoucherRepository{
    
    private VoucherService $Service;
    private VoucherDTO $Dto;
    private VoucherValidator $Validator;
    //private ErrMsgPopUp $ErrMsgPopUp;
    private string $RenderType;

    public function __construct()  {
        //$this->Dto = $Dto;
        //$this->Service = $Service;
        //$this->Validator = $Validator;
        //$this->ErrMsgPopUp = new ErrMsgPopUp();
        //$this->Dto->Accounts = $Dto->Accounts;
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
        //VcrSearchedData['voucher_id']

    public function JvJdDelete($Dto) {
            echo "delete来た";
            echo "<br><pre>1"; var_dump($Dto->VcrSearchedData);echo "</pr><br>";
            $VoucherId  =   $Dto->VcrSearchedData[0]['voucher_id'];
            echo "voucher_id = {$VoucherId}";exit;
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
        $IndexCount = count($Dto->DtoDetails);
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
            foreach ($Dto->DtoDetails as $RecNo => $Row){
                if($Row['side'] === 'debit') {
                    $stmtDetail->execute([
                        $voucherId,
                        $Row['account_id'],
                        'debit',
                        $Row['amount']
                    ]);
                } else {
                     $stmtDetail->execute([
                        $voucherId,
                        $Row['account_id'],
                        'credit',
                        $Row['amount']
                    ]);
                }
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function VcrListSearch($VcrDto) {

        if(!empty($VcrDto->Date)){
            $from = date('Y-m-d', strtotime($VcrDto->Date));
            $to =   date('Y-m-d', strtotime($VcrDto->Date));
        }
        if(
            !empty($VcrDto->VcrListDatePeriod['検索開始日付'] )   &&
            !empty($VcrDto->VcrListDatePeriod['検索終了日付'] )
        )
        {
            $from = date('Y-m-d', strtotime($VcrDto->VcrListDatePeriod['検索開始日付']));
            $to   =  date('Y-m-d', strtotime($VcrDto->VcrListDatePeriod['検索終了日付']));
        }

        if(empty($from) || empty($to)) {
            $from   =   '1970-01-01';
            $to     =   '2099-12-31';
        }

        $UserId = getLoginUserId();
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
        if (!empty($VcrDto->ListVcrNum)) {
            $sql .= " AND jv.id = :vchrnumber ";
        }
        if (!empty($VcrDto->Summary)) {
            $sql .= " AND jv.summary LIKE :vchrsummary ";
        }
        $sql .= " GROUP BY jd.voucher_id,jd.id";

        $stmt = $pdo->prepare($sql);    
        $params = [
            ':from'   => $from,
            ':to'     => $to,
            ':user_id' => $UserId
        ];
        if (!empty($VcrDto->ListVcrNum)) $params[':vchrnumber'] = $VcrDto->ListVcrNum;
        if (!empty($VcrDto->Summary))   $params[':vchrsummary'] = '%' . $VcrDto->Summary . '%';
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
