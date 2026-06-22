<?php
require_once ROOT_PATH . '/lib/helpers.php';

//class AccountsController {
//    public function add()
//    {
//        // 簡易なプレースホルダ実装。後で本実装へ置き換えてください。
//        $message = '';
//        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//            requireCsrf();
//            // TODO: アカウント追加処理をここに実装
//            $message = 'アカウント追加（ダミー）：処理は未実装です。';
//        }
//        $TokenKey = generateCsrfToken();
        // 最小ビューを表示
//        require ROOT_PATH . '/views/accounts/add.php';
//    }
//}

?>
<?php
require_once ROOT_PATH.'/app/services/AccountsService.php';
require_once ROOT_PATH.'/app/DTO/LoginDTO.php';

class AccountsController {
    private $Service;
    public function __construct()
    {
        $this->Service = new AccountsService();
        $this->Dto = new AccountsDto();
    }
    public function add()
    {
        $this->Service->GetAccounts;
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
            $TokenKey = $_POST['csrfTokenKey'];
        }else{
            $TokenKey = generateCsrfToken();
        }
        require ROOT_PATH.'/views/Accounts/AccountsView.php';
    }
}




//                $_SESSION['user'] = [
//                    'id' => (int)$user['id'],
//                    'username' => $user['username'],
//                    'email' => $user['email'],
//                    'fiscalMonth' => $user['fiscal_month'],
//                    'fiscalDay' => $user['fiscal_day']
//                ];
?>