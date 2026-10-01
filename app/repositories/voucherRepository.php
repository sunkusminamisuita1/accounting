<?php
require_once ROOT_PATH . '/app/services/voucherService.php';
require_once ROOT_PATH . '/app/dto/voucherDto.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/validators/voucherValidator.php';

class voucherRepository{
    //voucherRepository($this->pdo,$this->dto, $this->validator);
    private voucherService $service;
    private voucherDto $dto;
    private voucherValidator $validator;
    //private errMsgPopUp $errMsgPopUp;
    private string $renderType;
    private $pdo;

    public function __construct($pdo,$dto, $validator)  {
        $this->pdo              = $pdo;
        $this->dto              = $dto;
        $this->validator        = $validator;
    }

    public function findAllByUser(int $userId): array {
        // $pdo = getPDO();
        $stmt = $this->pdo->prepare("
            SELECT id, voucher_date, summary
            FROM journal_vouchers
            WHERE user_id = ?
            ORDER BY voucher_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id) {
        // $pdo = getPDO();
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM journal_vouchers
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data) {
        // $pdo = getPDO();
        $stmt = $this->pdo->prepare("
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
            //$pdo = getPDO();
            $this->pdo->beginTransaction();

            // 伝票に紐づく明細を削除
             $stmtDetails = $this->pdo->prepare("DELETE FROM journal_details WHERE voucher_id = ?");
             $stmtDetails->execute([$id]);

            // 伝票を削除
            $stmtVoucher = $this->pdo->prepare("DELETE FROM journal_vouchers WHERE id = ?");
            $stmtVoucher->execute([$id]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
        if ($stmtVoucher->rowCount() > $stmtDetails->rowCount()) {
            return $stmtVoucher->rowCount();
        } else {
            return $stmtDetails->rowCount();
        }
    }

    public function jvJdDelete() {
            $voucherId  =   $this->dto->vcrSearchedData[0]['voucher_id'];
        try{
            //$pdo = getPDO();
            $this->pdo->beginTransaction();

            // 伝票に紐づく明細を削除
            $stmtDetails = $this->pdo->prepare("DELETE FROM journal_details WHERE voucher_id = ?");
            $stmtDetails->execute([$voucherId]);

            // 伝票を削除
            $stmtVoucher = $this->pdo->prepare("DELETE FROM journal_vouchers WHERE id = ?");
            $stmtVoucher->execute([$voucherId]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
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
            $stmt = $this->pdo->query("
                SELECT *
                FROM accounts
                ORDER BY id
            ");
        } catch (Exception $e){
            throw $e;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insertVoucher(){
        $indexCount = count($this->dto->dtoDetails);
        //$pdo = getPDO();
        $this->pdo->beginTransaction();

        if( isset($_POST['vcrUpdate'])) {
            $voucherId  =   (int)$this->dto->vcrSearchedData[0]['id'];
        }

        try {
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO journal_vouchers
                    (voucher_date, summary, user_id, shop_code, created_at)
                    VALUES (?,?,?,?,?)"
            );
            $stmt->execute([
                $this->dto->date,
                $this->dto->summary  ,
                $_SESSION['user']['id'],
                $_SESSION['currentShopCode'] ?? '',
                date('Y-m-d H:i:s')
            ]);

            $voucherId = (int)$this->pdo->lastInsertId();
           
            $stmtDetail = $this->pdo->prepare("
                INSERT INTO journal_details
                    (voucher_id, jd_summary, account_id, side, amount)
                    VALUES (?,?,?,?,?)
            ");

            foreach ($this->dto->dtoDetails as $recNo => $row){
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
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function vcrListSearch() {
        //echo "current shop code : " . var_dump($_SESSION['currentShopCode']) . "<br>";exit;

        if(!empty($this->dto->date)){
            //echo "vcrrepo 186";
            $from = date('Y-m-d', strtotime($this->dto->date));
            $to =   date('Y-m-d', strtotime($this->dto->date));
        }
        if(
            !empty($this->dto->vcrListDatePeriod['検索開始日付'] )   &&
            !empty($this->dto->vcrListDatePeriod['検索終了日付'] )
        )
        {
            
            $from = date('Y-m-d', strtotime($this->dto->vcrListDatePeriod['検索開始日付']));
            $to   = date('Y-m-d', strtotime($this->dto->vcrListDatePeriod['検索終了日付']));
            //echo "vcrrepo 195  {$from}/{$to}";
        }

        if(empty($from) || empty($to)) {
            $from   =   '1970-01-01';
            $to     =   '2099-12-31';
        }

        $userId = getLoginUserId();
        //$pdo = getPDO();

         $sql = "SELECT 
                jv.id,
                jd.id as JdId,
                jv.voucher_date,
                jv.summary,
                jv.shop_code,
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
              AND jv.voucher_date BETWEEN :from AND :to
              ";

        // 条件がある場合だけ絞り込むロジック
        $params0 = [];
        if (trim($this->dto->session['currentShopCode']) !== 'all') {
            $sql .= " AND jv.shop_code = :shop_code ";
            $params0 = [':shop_code' => $this->dto->session['currentShopCode']];
        }

        if (!empty($this->dto->listVcrNum)) {
            $sql .= " AND jv.id = :vchrnumber ";
        }

        if (!empty($this->dto->summary)) {
            $sql .= " AND (jv.summary LIKE :vchrsummary OR jd.jd_summary LIKE :vchrsummary) ";
        }

        $sql .= " GROUP BY jd.voucher_id,jd.id";

        $stmt = $this->pdo->prepare($sql); 
        
        $params1 = [
            ':from'   => $from,
            ':to'     => $to,
            ':user_id' => $userId,
        ];
        $params = array_merge($params1, $params0);

        if (!empty($this->dto->listVcrNum)) $params[':vchrnumber'] = $this->dto->listVcrNum;
        if (!empty($this->dto->summary))   $params[':vchrsummary'] = '%' . $this->dto->summary . '%';
        // echo "<br>voucherrepository.php sql:";
        // print_r($sql);
        // echo "<br>params:";
        // print_r($params);
        //echo "vcrrepo line258<br>";exit;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

