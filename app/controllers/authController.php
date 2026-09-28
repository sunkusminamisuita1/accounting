<?php
require_once ROOT_PATH.'/app/services/authService.php';
require_once ROOT_PATH.'/app/dto/loginDto.php';
//require_once ROOT_PATH.'/app/dto/registerDto.php';
require_once ROOT_PATH.'/app/dto/shopsDto.php';
require_once ROOT_PATH.'/app/dto/authDto.php';
require_once ROOT_PATH.'/app/dto/loginDto.php';
require_once ROOT_PATH.'/lib/helpers.php';

class authController{
    private $authSvc;
    private $shopsSvc;
    private $authDto;
    private $shopsDto;
    private $pdo;

    public function __construct($pdo)
    {
        //$this->authDto     = new authDto();
        $this->shopsDto    = new shopsDto();
        //$this->registerDto = new registerDto();
        //$this->loginDto    = new loginDto();
        $this->pdo         = $pdo;
        //$this->authSvc     = new authService($this->authDto, $pdo);
        $this->shopsSvc    = new shopsService($this->shopsDto, $pdo);
        

    }
    public function login()
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            try {                
                $this->authDto = new authDto(
                                    '',                             //userName
                                    trim($_POST['email']),
                                    $_POST['password'],
                                    0,
                                    0
                                    );
                $this->authSvc     = new authService($this->authDto, $this->pdo);
                $this->authDto->user    = $this->authSvc->login();
                session_regenerate_id(true);
                $_SESSION['user'] =     $this->authDto->user; 
                //var_dump($_SESSION['user']);exit;
                $this->shopsDto->user['id'] = $_SESSION['user']['id'];
                //echo "shopsDto->user['id']={$this->shopsDto->user['id']}";exit;


                $this->shopsDto->userShops   = $this->shopsSvc->getShopsData();
                $_SESSION['userShops']      = $this->shopsDto->userShops;

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
                $this->authDto =  new authDto(
                                        trim($_POST['userName']),
                                        trim($_POST['email']),
                                        $_POST['password'],
                                        (int)$_POST['fiscal_month'],
                                        (int)$_POST['fiscal_day']
                );
                $this->authSvc     = new authService($this->authDto, $this->pdo);
                $this->authSvc->register();
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