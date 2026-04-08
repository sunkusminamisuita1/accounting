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

    public function insert(array $data): void
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            INSERT INTO users
            (username, email, password_hash, fiscal_month, fiscal_day)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['fiscal_month'],
            $data['fiscal_day']
        ]);
    }
}
?>