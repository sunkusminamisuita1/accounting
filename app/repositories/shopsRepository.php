<?php
class shopsRepository{

    private $dto;
    private $pdo;

    public function __construct($dto, $pdo)
    {
        $this->dto         = $dto;
        $this->pdo         = $pdo;
        //$this->authSvc     = new authService($this->authDto, $pdo);
        //$this->shopsSvc    = new shopsService($this->shopsDto, $pdo);
        
    }

    public function getShopsByUserId($delMode): array {
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
        //echo "getShopsByUserId={$this->dto->user['id']}";exit;

        $stmt = $this->pdo->prepare("
            SELECT id, user_id, shop_code, shop_name , open_date , address , closed , closed_date ,
                summary , created_at, deleted, edittype
                FROM shops WHERE 
                            (user_id = ?)                          AND 
                            (edittype IS NULL OR edittype <> ?)    AND
                            (deleted = ?)
        ");

        try {
            $stmt->execute([
                $this->dto->user['id'] ?? "",
                '削除',
                $delMode
            ]);
        } catch(Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function shopsAdd(?int $key = null): void {
        $this->pdo->beginTransaction();
        $createdAt = date('Y-m-d H:i:s');

        $rowsToInsert = [];
        $rowsToInsert = $this->dto->shopAltTbl[$key];
        $closedDate = trim((string)($rowsToInsert['closed_date'] ?? ''));
        $closedDateValue = null;
        if ($closedDate !== '') {
            // 同様に "YYYYMMDD" を "YYYY-MM-DD" に変換
            if (strlen($closedDate) === 8) {
                $closedDateValue = substr($closedDate, 0, 4) . '-' . substr($closedDate, 4, 2) . '-' . substr($closedDate, 6, 2);
            } else {
                $closedDateValue = $closedDate;
            }
        }

        $openDate = trim((string)($rowsToInsert['open_date'] ?? ''));
        $openDateValue = null;

        if ($openDate !== '') {
            // "20250101" を "2025-01-01" に変換する処理を追加
            if (strlen($openDate) === 8) {
                $openDateValue = substr($openDate, 0, 4) . '-' . substr($openDate, 4, 2) . '-' . substr($openDate, 6, 2);
            } else {
                $openDateValue = $openDate;
            }
        }

        //var_dump($rowsToInsert);

        if (($rowsToInsert['editType'] ?? '') !== '追加') {
            echo "ShopRepository.shopsAdd 論理エラー　edittyeが追加でない";
            exit;
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
            deleted,
            edittype
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([
                $rowsToInsert['user_id'] ?? $this->dto->user['id'] ?? null,
                $rowsToInsert['shop_code'] ?? null,
                $rowsToInsert['shop_name'] ?? null,
                $openDateValue,
                (int)($rowsToInsert['closed'] ?? 0),
                $closedDateValue,
                $rowsToInsert['summary'] ?? null,
                $createdAt,
                $rowsToInsert['deleted'] ?? 0,
                ''
            ]);
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
    }

    public function shopsAlt(?int $key = null): void {
        $this->pdo->beginTransaction();

        $createdAt = date('Y-m-d H:i:s');

        $rowsToAlt = [];
        $rowsToAlt = $this->dto->shopAltTbl[$key];
        //var_dump($rowsToAlt);
        $openDate = trim((string)($rowsToAlt['open_date'] ?? ''));
        $openDateValue = $openDate === '' ? null : $openDate;

        $closedDate = trim((string)($rowsToAlt['closed_date'] ?? ''));
        $closedDateValue = $closedDate === '' ? null : $closedDate;

        $stmt = $this->pdo->prepare("UPDATE shops SET
                                    user_id         = ?,
                                    shop_code       = ?,
                                    shop_name       = ?,
                                    open_date       = ?,
                                    closed          = ?,
                                    closed_date     = ?,
                                    summary         = ?,
                                    deleted         = ?,
                                    edittype        = ?
                                WHERE
                                    shop_code       = ?
        ");

        if (($rowsToAlt['editType'] ?? '') !== '更新') {
            echo "ShopRepository.shopsAlt 論理エラー EditTypeが不正";
        }

        try {
        $stmt->execute([
            $rowsToAlt['user_id'] ?? $this->dto->user['id'] ?? null,
            $rowsToAlt['shop_code'] ?? null,
            $rowsToAlt['shop_name'] ?? null,
            $openDate,
            (int)($rowsToAlt['closed'] ?? 0),
            $closedDateValue,
            $rowsToAlt['summary'] ?? null,
            $rowsToAlt['deleted'] ?? 0,
            '',
            $rowsToAlt['shop_code'] ?? null
        ]);
        $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
    }

}
?>