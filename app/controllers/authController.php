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
                require_once ROOT_PATH.'/app/Dto/loginDto.php';
                $Dto = new loginDto(
                                    trim($_POST['email']),
                                    $_POST['password']
                                    );
                $Dto->User      = $this->service->login($Dto);
                session_regenerate_id(true);
                $_SESSION['user'] =     $Dto->User; 

                $Dto->UserShops         = $this->shopsSvc->getShopsData($Dto);
                $_SESSION['UserShops']  = $Dto->UserShops;

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
                require_once ROOT_PATH.'/app/Dto/RegisterDto.php';
                $Dto =  new RegisterDto(
                                        trim($_POST['username']),
                                        trim($_POST['email']),
                                        $_POST['password'],
                                        (int)$_POST['fiscal_month'],
                                        (int)$_POST['fiscal_day']
                );
                $this->service->register($Dto);
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