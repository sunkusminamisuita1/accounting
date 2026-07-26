<?php
class ShopsRepository{

    public function getShopsByUserId($Dto): array {
        // +-------------+--------------+------+-----+---------------------+----------------+
        // | Field       | Type         | Null | Key | Default             | Extra          |
        // +-------------+--------------+------+-----+---------------------+----------------+
        // | id          | int(11)      | NO   | PRI | NULL                | auto_increment |
        // | user_id     | int(11)      | NO   | MUL | NULL                |                |
        // | shop_code   | varchar(20)  | NO   |     | NULL                |                |
        // | shop_name   | varchar(100) | NO   |     | NULL                |                |
        // | open_date   | date         | YES  |     | NULL                |                |
        // | address     | varchar(255) | YES  |     | NULL                |                |
        // | closed      | int(11)      | YES  |     | NULL                |                |
        // | closed_date | date         | YES  |     | NULL                |                |
        // | summary     | varchar(255) | YES  |     | NULL                |                |
        // | created_at  | timestamp    | YES  |     | current_timestamp() |                |
        // | edittype    | varchar(255) | YES  |     | NULL                |                |
        // +-------------+--------------+------+-----+---------------------+----------------+

        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT id, shop_code, shop_name , open_date , address , closed , closed_date , summary , edittype
                FROM shops WHERE user_id = ? AND (edittype IS NULL OR edittype <> ?)
        ");

        try {
            $stmt->execute([
                $Dto->User['id'] ?? "",
                '削除'
            ]);
        } catch(Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ShopsAdd($Dto, ?int $Key = null): void {
        $pdo = getPDO();
        $CreatedAt = date('Y-m-d H:i:s');

        $RowsToInsert = [];
        if ($Key !== null && isset($Dto->ShopAltTbl[$Key])) {
            $RowsToInsert[] = $Dto->ShopAltTbl[$Key];
        } else {
            $RowsToInsert = $Dto->ShopAltTbl ?? [];
        }

        foreach ($RowsToInsert as $Row) {
            if (($Row['edittype'] ?? '') !== '追加') {
                continue;
            }

            $sql = "INSERT INTO shops (
                user_id,
                shop_code,
                shop_name,
                open_date,
                closed,
                closed_date,
                summary,
                created_at,
                edittype
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute([
                    $Row['user_id'] ?? $Dto->User['id'] ?? null,
                    $Row['shop_code'] ?? null,
                    $Row['shop_name'] ?? null,
                    $Row['open_date'] ?? null,
                    (int)($Row['closed'] ?? 0),
                    $Row['closed_date'] ?? null,
                    $Row['summary'] ?? null,
                    $CreatedAt,
                    null
                ]);
            } catch (Exception $e) {
                $message = $e->getMessage();
                echo $message;
                throw $e;
            }
        }
    }

    public function ShopsAlt($Dto, ?int $Key = null): void {
        $pdo = getPDO();
        $CreatedAt = date('Y-m-d H:i:s');

        $RowsToInsert = [];
        if ($Key !== null && isset($Dto->ShopAltTbl[$Key])) 
        {
            $RowsToInsert[] = $Dto->ShopAltTbl[$Key];
        } else {
            $RowsToInsert = $Dto->ShopAltTbl ?? [];
        }

            $stmt = $pdo->prepare("UPDATE shops SET
                user_id         = ?,
                shop_code       = ?,
                shop_name       = ?,
                open_date       = ?,
                closed          = ?,
                closed_date     = ?,
                summary         = ?,
                created_at      = ?,
                edittype        = ?
            ");

        foreach ($RowsToInsert as $Row) {
            if (($Row['edittype'] ?? '') !== '更新') {
                continue;
            }

            try {
            $stmt->execute([
                $Row['user_id'] ?? $Dto->User['id'] ?? null,
                $Row['shop_code'] ?? null,
                $Row['shop_name'] ?? null,
                $Row['open_date'] ?? null,
                (int)($Row['closed'] ?? 0),
                $Row['closed_date'] ?? null,
                $Row['summary'] ?? null,
                $Row['created_at']?? null,
                null
            ]);

            } catch (Exception $e) {
                $message = $e->getMessage();
                echo $message;
                throw $e;
            }
        }
    }

}
?>