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
                ORDER BY type,name
            ");
        } catch(Exception $e) {
            echo $message;
            $message = $e->getMessage();
            throw $e;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function AcctDelete(AccountsDto $Dto) {
        try{
            $pdo = getPDO();
            $pdo->beginTransaction();

            // 該当ユーザーIDの勘定科目テーブルを削除
            $stmtVoucher = $pdo->prepare("DELETE FROM accounts WHERE user_id = ?");
            $stmtVoucher->execute($Dto->$id);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

    public function AcctCreate(AccountsDto $Dto) {
        $pdo = getPDO();
        $pdo->beginTransaction();
        try {
            
            $stmt = $pdo->prepare("
                INSERT INTO accounts
                    (id, user_id, name, type)
                    VALUES (?,?,?,?)
            ");

            foreach ($Dto->AcctAltTbl as $RecNo => $Row){
                $stmtDetail->execute([
                    '',
                    $Row['user_id'] ?? "" ,
                    $Row['name'] ?? "",
                    $Row['type'] ?? ""
                ]);
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

    }

}
?>
