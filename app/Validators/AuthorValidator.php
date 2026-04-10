<?php
// app/validators/AuthValidator.php
class AuthValidator
{
    public function validateLogin(LoginDTO $dto): void
    {
        if (empty($dto->email)) {
            throw new Exception('メールアドレスは必須です');
        }

        if (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('メール形式が不正です');
        }

        if (empty($dto->password)) {
            throw new Exception('パスワードは必須です');
        }
    }

    public function validateRegister(RegisterDTO $dto): void
    {
        if (empty($dto->username)) {
            throw new Exception('ユーザー名は必須です');
        }

        if (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('メール形式が不正です');
        }

        if (strlen($dto->password) < 8) {
            throw new Exception('パスワードは8文字以上必要です');
        }

        if ($dto->fiscalMonth < 1 || $dto->fiscalMonth > 12) {
            throw new Exception('決算月が不正です');
        }

        if ($dto->fiscalDay < 1 || $dto->fiscalDay > 31) {
            throw new Exception('決算日が不正です');
        }
    }
}
?>