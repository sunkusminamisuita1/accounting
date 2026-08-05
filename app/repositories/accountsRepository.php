<?php
require_once ROOT_PATH.'/app/services/authService.php';
require_once ROOT_PATH.'/app/repositories/userRepository.php';
require_once ROOT_PATH.'/app/repositories/voucherRepository.php';
require_once ROOT_PATH.'/app/dto/accountsDto.php';

class accountsRepository
{
    public accountsDto          $ctrDto;

    public function __construct(accountsDto $dto)    {
    }

    public function getaccounts(accountsDto $dto, bool $includeDeleted = false)  {
        try{
            //$pdo = getPDO();
            //$stmt = $pdo->query("
            //    SELECT id, user_id, name, type
            //    FROM accounts
            //    WHERE is_deleted = 0
            //    ORDER BY type,name
            //");

            $Where = $includeDeleted ? "" : "WHERE is_deleted = 0";
            $pdo = getPDO();
            $stmt = $pdo->query("
                SELECT id, user_id, name, type, is_deleted
                FROM accounts
                $Where
                ORDER BY type,name
            ");

            //$pdo = getPDO();
            //$stmt = $pdo->prepare("
            //    SELECT id, user_id, name, type
            //    FROM accounts
            //    WHERE is_deleted = ?
            //    ORDER BY type,name
            //");
            //$stmt->execute([
            //    $_GET['route'] === 'accounts.edit' ? 1 : 0
            //]);

        } catch(Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //public function AcctDelete(accountsDto $dto) {
    //    try{
    //        $pdo = getPDO();
    //        $pdo->beginTransaction();

            // 該当ユーザーIDの勘定科目テーブルを削除
    //        $stmtVoucher = $pdo->prepare("DELETE FROM accounts WHERE user_id = ?");
    //        $stmtVoucher->execute($dto->id);

    //        $pdo->commit();
    //    } catch (Exception $e) {
    //        $pdo->rollBack();
    //        throw $e;
    //    }

    //}

    public function acctAdd(accountsDto $dto , $key) {
        $pdo = getPDO();
        $pdo->beginTransaction();
        try {
            
            $stmt = $pdo->prepare("
                INSERT INTO accounts
                    (id, user_id, name, type)
                    VALUES (?,?,?,?)
            ");

            $stmt->execute([
                null,
                $dto->acctAltTbl[$key]['user_id'] ?? "" ,
                $dto->acctAltTbl[$key]['name'] ?? "",
                $dto->acctAltTbl[$key]['type'] ?? ""
            ]);
            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

    public function acctEdit(accountsDto $dto , $key) {
        $pdo = getPDO();
        echo "<br>repo-edit name=".$dto->acctAltTbl[$key]['name'].   " type=".$dto->acctAltTbl[$key]['type']. 
              "id=".$dto->acctAltTbl[$key]['id']. "user_id=".$dto->acctAltTbl[$key]['user_id']. "<br>";
        $pdo->beginTransaction();
        try {
            
            $stmt = $pdo->prepare("
                UPDATE accounts
                    SET name = ?, type = ?, is_deleted = ?
                    WHERE id = ? AND user_id = ? 
            ");

            $stmt->execute([
                $dto->acctAltTbl[$key]['name'] ?? "",
                $dto->acctAltTbl[$key]['type'] ?? "",
                $dto->acctAltTbl[$key]['is_deleted'] ?? 0,
                $dto->acctAltTbl[$key]['id'] ?? "",
                $dto->acctAltTbl[$key]['user_id'] ?? "" 
            ]);
            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

    public function AcctDlt(accountsDto $dto , $key) {
        $pdo = getPDO();
        $pdo->beginTransaction();
        try {
            
            $stmt = $pdo->prepare("
                UPDATE accounts
                    SET is_deleted = ?
                    WHERE id = ? AND user_id = ?
            ");

            $stmt->execute([
                1,
                $dto->acctAltTbl[$key]['id'] ?? "",
                $dto->acctAltTbl[$key]['user_id'] ?? ""
            ]);
            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

}
?>
