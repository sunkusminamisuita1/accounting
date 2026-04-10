<?php
class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT id, username, email, password_hash,
                   fiscal_month, fiscal_day
            FROM users
            WHERE email = ?
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function insert($dto): void
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            INSERT INTO users
            (username, email, password_hash, fiscal_month, fiscal_day)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $dto->username,
            $dto->email,
            $dto->password,
            $dto->fiscal_month,
            $dto->fiscal_day
        ]);
    }
}
?>