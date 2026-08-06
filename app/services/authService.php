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

    public function login(loginDto $dto): array
    {
        $user = $this->repo->findByEmail($dto->email);

        if (!$user || !password_verify($dto->password, $user['password_hash'])) {
            throw new Exception('ログイン失敗');
        }else {
                $dto->user = $user;
        }
        return $user;
    }
    public function register(registerDto $dto): void
    {
        // バリデーション
        if (empty($dto->email) || empty($dto->password)) {
            throw new Exception('必須項目が未入力です');
        }

        $dto->password = password_hash($dto->password, PASSWORD_DEFAULT);

        $this->repo->insert($dto);
    }

}