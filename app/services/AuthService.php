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

    public function login(string $email, string $password): array
    {
        $user = $this->repo->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception('メールアドレスまたはパスワードが間違っています。');
        }

        return $user;
    }

    public function register(array $data): void
    {
        // バリデーション
        if (empty($data['email']) || empty($data['password'])) {
            throw new Exception('必須項目が未入力です');
        }

        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $this->repo->insert($data);
    }
}