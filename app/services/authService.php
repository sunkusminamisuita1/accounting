<?php
// app/services/authService.php
require_once ROOT_PATH.'/app/repositories/userRepository.php';
require_once ROOT_PATH.'/app/services/shopsService.php';

class authService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new userRepository();
    }

    public function login(loginDto $Dto): array
    {
        $user = $this->repo->findByEmail($Dto->email);
        $Dto->User = $user;
        if (!$user || !password_verify($Dto->password, $user['password_hash'])) {
            throw new Exception('ログイン失敗');
        }
        return $user;
    }
    public function register(RegisterDto $Dto): void
    {
        // バリデーション
        if (empty($Dto->email) || empty($Dto->password)) {
            throw new Exception('必須項目が未入力です');
        }

        $Dto->password = password_hash($Dto->password, PASSWORD_DEFAULT);

        $this->repo->insert($Dto);
    }

}