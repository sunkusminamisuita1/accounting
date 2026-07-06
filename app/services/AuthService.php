<?php
// app/services/AuthService.php
require_once ROOT_PATH.'/app/repositories/UserRepository.php';

class AuthService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

    public function login(LoginDTO $dto): array
    {
        $user = $this->repo->findByEmail($dto->email);
        $dto->User = $user;
        if (!$user || !password_verify($dto->password, $user['password_hash'])) {
            throw new Exception('ログイン失敗');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = 
            [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'fiscalMonth' => $user['fiscal_month'],
                'fiscalDay' => $user['fiscal_day']
            ];       
        return $user;
    }
    public function register(RegisterDTO $dto): void
    {
        // バリデーション
        if (empty($dto->email) || empty($dto->password)) {
            throw new Exception('必須項目が未入力です');
        }

        $dto->password = password_hash($dto->password, PASSWORD_DEFAULT);

        $this->repo->insert($dto);
    }



    public function getShopsData(LoginDTO $dto): array
    {
        $dto->shopList = $this->repo->getShopsByUserId($dto);
        $_SESSION['user_shops'] = $dto->shopList;
        // 初期選択店舗として、リストの先頭にある店舗のIDを「現在の操作店舗」としてセット
        if (!empty($dto->shopList)) {
            $_SESSION['current_shop_id'] = $dto->shopList[0]['id']; 
            $_SESSION['current_shop_name'] = $dto->shopList[0]['shop_name'];
        } else {
        // 店舗が未登録の場合のフォールバック
            $_SESSION['current_shop_id'] = 0;
            $_SESSION['current_shop_name'] = "店舗未登録";
        }
        return $dto->shopList;
    }





}