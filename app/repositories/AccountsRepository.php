<?php
require_once ROOT_PATH.'/app/services/AuthService.php';
require_once ROOT_PATH.'/app/repositories/UserRepository.php';
require_once ROOT_PATH.'/app/repositories/voucherRepository.php';
require_once ROOT_PATH.'/app/DTO/AccountsDto.php';

class AccountsRepository
{
    public AccountsDto          $CtrDto;

    public function __construct(AccountsDto $CtrDto)    {
        $this->CtrDto   = $CtrDto;
    }

    public function getAccounts()  {
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

}
?>
