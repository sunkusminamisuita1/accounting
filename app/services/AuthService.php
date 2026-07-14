<?php
// app/services/AuthService.php
require_once ROOT_PATH.'/app/repositories/UserRepository.php';
require_once ROOT_PATH.'/app/services/shopsService.php';

class AuthService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

    public function login(LoginDTO $Dto): array
    {
        $user = $this->repo->findByEmail($Dto->email);
        $Dto->User = $user;
        if (!$user || !password_verify($Dto->password, $user['password_hash'])) {
            throw new Exception('ログイン失敗');
        }
        return $user;
    }
    public function register(RegisterDTO $Dto): void
    {
        // バリデーション
        if (empty($Dto->email) || empty($Dto->password)) {
            throw new Exception('必須項目が未入力です');
        }

        $Dto->password = password_hash($Dto->password, PASSWORD_DEFAULT);

        $this->repo->insert($Dto);
    }

}