<?php
class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT  id, username, email, password_hash,
                    fiscal_month, 
                    fiscal_day
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
            $dto->fiscalMonth,
            $dto->fiscalDay
        ]);
    }

    public function getShopsByUserId($Dto): array
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT id, shop_code, shop_name 
                FROM shops WHERE user_id = ?
        ");

        try {
            $stmt->execute([$Dto->User['id'] ?? ""]);  // Use null coalescing operator to handle undefined index
        } catch(Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
