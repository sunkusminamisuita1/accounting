<?php
require_once ROOT_PATH.'/app/services/AuthService.php';
class AuthController{
    private $service;
    public function __construct()
    {
        $this->service = new AuthService();
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
                $user = $this->service->login($dto);



            //    $user = $this->service->login(
            //        trim($_POST['email']),
            //        $_POST['password']
            //    );
                // SESSIONはControllerでのみ扱う
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



//    public function register()
//    {
//        $message = '';
//        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//            requirePost();
//            verifyCsrfToken($_POST['csrfTokenKey'] ?? '');
//            try {
//                $this->service->register([
//                    'username' => trim($_POST['username']),
//                    'email' => trim($_POST['email']),
//                    'password' => $_POST['password'],
//                    'fiscal_month' => (int)$_POST['fiscal_month'],
//                    'fiscal_day' => (int)$_POST['fiscal_day']
//                ]);
//                header('Location: index.php?route=login');
//                exit;
//            } catch (Exception $e) {
//                $message = $e->getMessage();
//            }
//        }
//        $TokenKey = generateCsrfToken();
//        require ROOT_PATH.'/views/auth/register.php';
//    }




}
?>