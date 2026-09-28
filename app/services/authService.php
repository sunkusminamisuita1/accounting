<?php
// app/services/authService.php
require_once ROOT_PATH.'/app/repositories/userRepository.php';
require_once ROOT_PATH.'/app/services/shopsService.php';



class authService
{

    private $repo;
    private $authDto;
    private $pdo;
    private $vali;
    private $shopsDto;

    public function __construct($dto,$pdo)
    {
        $this->authDto              =   $dto;
        $this->shopsDto             =   new shopsDto();
        $this->pdo                  =   $pdo;
        $this->repo                 =   new userRepository($dto, $pdo);
        $this->shopsDto->email      =   $this->authDto->email;
        $this->shopsDto->password   =   $this->authDto->password;

        $this->vali  = new shopsValidator($this->shopsDto, $pdo, false);

    }

    public function login(): array
    {
        $user = $this->repo->findByEmail($this->authDto->email);

        if (!$user || !password_verify($this->authDto->password, $user['password_hash'])) {
            throw new Exception('ログイン失敗');
        }else {
                $this->authDto->user = $user;
        }
        return $user;
    }
    public function register(): void
    {
        // バリデーション
        if (empty($this->authDto->email) || empty($this->authDto->password)) {
            throw new Exception('必須項目が未入力です');
        }

        $this->authDto->password = password_hash($this->authDto->password, PASSWORD_DEFAULT);

        $this->repo->insert($this->authDto);
    }

}