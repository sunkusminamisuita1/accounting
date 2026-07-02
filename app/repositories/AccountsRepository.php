<?php
require_once ROOT_PATH.'/app/services/AuthService.php';
require_once ROOT_PATH.'/app/repositories/UserRepository.php';
require_once ROOT_PATH.'/app/repositories/voucherRepository.php';
require_once ROOT_PATH.'/app/DTO/AccountsDto.php';

class AccountsRepository
{
    public AccountsDto          $CtrDto;

    public function __construct(AccountsDto $Dto)    {
    }

    public function getAccounts(AccountsDto $Dto)  {
        try{
            $pdo = getPDO();
            $stmt = $pdo->query("
                SELECT id, user_id, name, type
                FROM accounts
                WHERE is_deleted = 0
                ORDER BY type,name
            ");
        } catch(Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //public function AcctDelete(AccountsDto $Dto) {
    //    try{
    //        $pdo = getPDO();
    //        $pdo->beginTransaction();

            // 該当ユーザーIDの勘定科目テーブルを削除
    //        $stmtVoucher = $pdo->prepare("DELETE FROM accounts WHERE user_id = ?");
    //        $stmtVoucher->execute($Dto->id);

    //        $pdo->commit();
    //    } catch (Exception $e) {
    //        $pdo->rollBack();
    //        throw $e;
    //    }

    //}

    public function AcctAdd(AccountsDto $Dto , $Key) {
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
                $Dto->AcctAltTbl[$Key]['user_id'] ?? "" ,
                $Dto->AcctAltTbl[$Key]['name'] ?? "",
                $Dto->AcctAltTbl[$Key]['type'] ?? ""
            ]);
            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

    public function AcctEdit(AccountsDto $Dto , $Key) {
        $pdo = getPDO();
        echo "<br>repo-edit key=".$Key . "<br>";
        $pdo->beginTransaction();
        try {
            
            $stmt = $pdo->prepare("
                UPDATE accounts
                    SET name = ?, type = ?
                    WHERE id = ? AND user_id = ?
            ");

            $stmt->execute([
                $Dto->AcctAltTbl[$Key]['name'] ?? "",
                $Dto->AcctAltTbl[$Key]['type'] ?? "",
                $Dto->AcctAltTbl[$Key]['id'] ?? "",
                $Dto->AcctAltTbl[$Key]['user_id'] ?? "" 
            ]);
            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

    public function AcctDlt(AccountsDto $Dto , $Key) {
        $pdo = getPDO();
        $pdo->beginTransaction();
        try {
            
            $stmt = $pdo->prepare("
                UPDATE accounts
                    SET is_deleted = ?
                    WHERE id = $Key
            ");

            $stmt->execute([
                1
            ]);
            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

}
?>
