<?php
require_once ROOT_PATH.'/app/services/authService.php';

require_once ROOT_PATH.'/lib/helpers.php';
class authController{
    private $service;
    private $shopsSvc;
    public function __construct()
    {
        $this->service  = new authService();
        $this->shopsSvc = new shopsService();
    }
    public function login()
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            try {
                require_once ROOT_PATH.'/app/dto/loginDto.php';
                $dto = new loginDto(
                                    trim($_POST['email']),
                                    $_POST['password']
                                    );
                $dto->user      = $this->service->login($dto);
                session_regenerate_id(true);
                $_SESSION['user'] =     $dto->user; 

                $dto->userShops         = $this->shopsSvc->getShopsData($dto);
                $_SESSION['userShops']  = $dto->userShops;

                header('location: index.php?route=home');
                exit;
            } 
            catch (Exception $e) {
                $message = $e->getMessage();
            }
            $tokenKey = $_POST['csrfTokenKey'];
        }else{
            $tokenKey = generateCsrfToken();
        }
        require ROOT_PATH.'/views/login.php';
    }

    public function register()
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            try {
                require_once ROOT_PATH.'/app/dto/registerDto.php';
                $dto =  new registerDto(
                                        trim($_POST['username']),
                                        trim($_POST['email']),
                                        $_POST['password'],
                                        (int)$_POST['fiscal_month'],
                                        (int)$_POST['fiscal_day']
                );
                $this->service->register($dto);
                header('location: index.php?route=login');
                exit;
            } catch (Exception $e) {
                $message = $e->getMessage();
            }
        }
        $tokenKey = generateCsrfToken();
        require ROOT_PATH.'/views/register.php';
    }
}
?>