<?php

//                $_SESSION['user'] = [
//                    'id' => (int)$user['id'],
//                    'username' => $user['username'],
//                    'email' => $user['email'],
//                    'fiscalMonth' => $user['fiscal_month'],
//                    'fiscalDay' => $user['fiscal_day']
//                ];
require_once ROOT_PATH . '/app/services/AccountsService.php';
require_once ROOT_PATH . '/app/DTO/AccountsDTO.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/Validators/AccountsValidator.php';
require_once ROOT_PATH . '/app/repositories/AccountsRepository.php';

class AccountsController {
    private AccountsService     $Service;
    private AccountsDTO         $Dto;
    private AccountsValidator   $Validator;
    private AccountsRepository  $Repo;
    public function __construct()
    {
        $this->Service      = new AccountsService();
        $this->Dto          = new AccountsDto();
        $this->Validator    = new AccountsValidator();
        $this->Repo         = new AccountsRepository();

    }
    public function add()
    {
        $this->Service->GetAccounts();
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            try {
                $dto = new LoginDTO(
                                    trim($_POST['email']),
                                    $_POST['password']
                                    );
                $user = $this->service->login($dto);
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'fiscalMonth' => $user['fiscal_month'],
                    'fiscalDay' => $user['fiscal_day']
                ];
                header('Location: index.php?route=home');
                exit;
            } 
            catch (Exception $e) {
                $message = $e->getMessage();
            }
        }
            $TokenKey = generateCsrfToken();
        require ROOT_PATH.'/views/Accounts/AccountsView.php';
    }
}





?>