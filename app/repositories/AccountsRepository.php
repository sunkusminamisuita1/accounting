<?php
// app/services/AuthService.php
//require_once ROOT_PATH.'/app/repositories/UserRepository.php';
//require_once ROOT_PATH.'/app/repositories/voucherRepository.php';
//require_once ROOT_PATH.'/app/DTO/AccountsDTO.php';

class AccountsRepository
{


	    public function getAccounts()  {
        $pdo = getPDO();
        $stmt = $pdo->query("
            SELECT id, name, type
            FROM accounts
            ORDER BY id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    	}

}
?>
