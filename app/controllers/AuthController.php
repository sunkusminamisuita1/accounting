<?php
require_once ROOT_PATH.'/app/services/AuthService.php';
require_once ROOT_PATH.'/app/services/shopsService.php';
require_once ROOT_PATH.'/lib/helpers.php';
class AuthController{
    private $service;
    private $shopsSvc;
    public function __construct()
    {
        $this->service  = new AuthService();
        $this->shopsSvc = new shopsService();
    }
    public function login()
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            try {
                require_once ROOT_PATH.'/app/DTO/LoginDTO.php';
                $dto = new LoginDTO(
                                    trim($_POST['email']),
                                    $_POST['password']
                                    );
                $dto->User = $this->service->login($dto);
                //var_dump($dto->User);exit;
                $_SESSION['user_shops']  = $this->shopsSvc->getShopsData($dto);
                header('Location: index.php?route=home');
                exit;
            } 
            catch (Exception $e) {
                $message = $e->getMessage();
            }
            $TokenKey = $_POST['csrfTokenKey'];
        }else{
            $TokenKey = generateCsrfToken();
        }
        require ROOT_PATH.'/views/auth/login.php';
    }

    public function register()
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            try {
                require_once ROOT_PATH.'/app/DTO/RegisterDTO.php';
                $dto =  new RegisterDTO(
                                        trim($_POST['username']),
                                        trim($_POST['email']),
                                        $_POST['password'],
                                        (int)$_POST['fiscal_month'],
                                        (int)$_POST['fiscal_day']
                );
                $this->service->register($dto);
                header('Location: index.php?route=login');
                exit;
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }
        $TokenKey = generateCsrfToken();
        require ROOT_PATH.'/views/auth/register.php';
    }
}
?>