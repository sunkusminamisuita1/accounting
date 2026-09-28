<?php
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
require_once ROOT_PATH.'/app/services/authService.php';
require_once ROOT_PATH.'/app/repositories/userRepository.php';
require_once ROOT_PATH.'/app/repositories/voucherRepository.php';
require_once ROOT_PATH.'/app/dto/accountsDto.php';
require_once ROOT_PATH . '/lib/helpers.php';


class accountsRepository
{
    private     $dto;
    private     $pdo;
    private     bool $debugMode;

    public function __construct(accountsDto $dto, $pdo)    {
        $this->dto  = $dto;
        $this->pdo  = $pdo;
        $this->debugMode = true; 
    }

    public function getAccounts(bool $includeDeleted = true)  {
        if ($this->dto->shopCode === null || $this->dto->shopCode === '') {
            throw new InvalidArgumentException('shopCode is required.');
        }

        if ($this->dto->id === null || $this->dto->id === '') {
            throw new InvalidArgumentException('userId is required.');
        }
        try{
            if($includeDeleted) {
                $Where0 = "WHERE ( is_deleted = 0  OR  is_deleted =  1 ) ";
            } else {
                $Where0 = "WHERE is_deleted = 0 ";
            }
            //str_contains(検索対象文字列, 探したい文字列)
            if(str_contains($this->dto->shopCode, 'all')) {
                $allShopCode = "";
            } else {
                $allShopCode = " AND shop_code = " . $this->dto->shopCode ;
            }

            $Where = $Where0 . " AND user_id = " . $this->dto->id . $allShopCode;
            //var_dump($this->dto->shopCode);exit;
            //echo $Where;exit;
            $stmt = $this->pdo->query("
                SELECT id, user_id, shop_code, name, type, sort_order, is_deleted
                FROM accounts
                $Where
                ORDER BY type,name
            ");

        } catch(Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getJournalDtails($shopCode, $userId){
        if ($shopCode === null || $shopCode === '') {
            throw new InvalidArgumentException('shopCode is required.');
        }

        if ($userId === null || $userId === '') {
            throw new InvalidArgumentException('userId is required.');
        }
        try{
            $stmt = $this->pdo->prepare("
                SELECT  jv.shop_code, 
                        jv.user_id, 
                        jd.account_id  
                    FROM journal_vouchers AS jv
                JOIN journal_details AS jd ON jv.id = jd.voucher_id
                WHERE jv.shop_code = ? AND jv.user_id = ?
                ORDER BY jv.id, jd.account_id
            ");
            $stmt->execute([$shopCode, $userId]);

        } catch(Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    public function acctAdd($key) {
        $this->pdo->beginTransaction();
        try {
            
            $stmt = $this->pdo->prepare("
                INSERT INTO accounts
                    (id, user_id, shop_code, name, type, sort_order, is_deleted)
                    VALUES (?,?,?,?,?,?,?)
            ");

            $stmt->execute([
                null,
                $this->dto->acctAltTbl[$key]['user_id'] ?? "" ,
                $this->dto->acctAltTbl[$key]['shop_code'] ?? "",
                $this->dto->acctAltTbl[$key]['name'] ?? "",
                $this->dto->acctAltTbl[$key]['type'] ?? "",
                $this->dto->acctAltTbl[$key]['sort_order'] ?? "",
                $this->dto->acctAltTbl[$key]['is_deleted'] ?? "0"
            ]);
            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

    }

    public function acctEdit($key) {
        $this->pdo->beginTransaction();
        try {
            
            $stmt = $this->pdo->prepare("
                UPDATE accounts
                    SET name = ?, type = ?, sort_order = ?, is_deleted = ?
                    WHERE id = ? AND user_id = ? 
            ");

            $stmt->execute([
                $this->dto->acctAltTbl[$key]['name'] ?? "",
                $this->dto->acctAltTbl[$key]['type'] ?? "",
                $this->dto->acctAltTbl[$key]['sort_order'] ?? "",
                $this->dto->acctAltTbl[$key]['is_deleted'] ?? 0,
                $this->dto->acctAltTbl[$key]['id'] ?? "",
                $this->dto->acctAltTbl[$key]['user_id'] ?? "" 
            ]);
            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

    }

    public function acctDlt($key) {
        $this->pdo->beginTransaction();
        try {
            
            $stmt = $this->pdo->prepare("
                DELETE FROM accounts
                    WHERE id = ? AND user_id = ?
            ");

            $stmt->execute([
                $this->dto->acctAltTbl[$key]['id'] ?? "",
                $this->dto->acctAltTbl[$key]['user_id'] ?? ""
            ]);
            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

    }

}
?>
