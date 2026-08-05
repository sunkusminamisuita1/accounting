<?php
class shopsRepository{

    public function getShopsByUserId($dto): array {
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
            SELECT id, user_id, shop_code, shop_name , open_date , address , closed , closed_date ,
                summary , created_at, deleted, edittype
                FROM shops WHERE user_id = ? AND (edittype IS NULL OR edittype <> ?)
        ");

        try {
            $stmt->execute([
                $dto->user['id'] ?? "",
                '削除'
            ]);
        } catch(Exception $e) {
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ShopsAdd($dto, ?int $key = null): void {
        $pdo = getPDO();
        $pdo->beginTransaction();
        $createdAt = date('Y-m-d H:i:s');

        $rowsToInsert = [];
        $rowsToInsert = $dto->shopAltTbl[$key];

        // 🛠️ デバッグ用：000002の時だけ通して、000001の時は強制終了して止める
        // if (($rowsToInsert['shop_code'] ?? '') === '000001') {
        //     echo "【デバッグ】なぜか古い店舗コード(000001)のデータでShopsAddが呼ばれました！<br>";
        //     echo "渡されたキー(Key)は: " . $key . " です。<br>";
        //     echo "トレース情報:<br>";
        //     debug_print_backtrace(); // どこから呼び出されたかを逆引き表示
        //     exit;
        // }
        // if(  $rowsToInsert['editType'] ){
        //     echo "<br>読み飛ばし{$rowsToInsert['shop_code']}<br>";
        //     var_dump($rowsToInsert['editType']);
        //     exit;
        // }

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
            echo "ShopRepository.ShopsAdd 論理エラー　edittyeが追加でない";
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

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                $rowsToInsert['user_id'] ?? $dto->user['id'] ?? null,
                $rowsToInsert['shop_code'] ?? null,
                $rowsToInsert['shop_name'] ?? null,
                $openDateValue,
                (int)($rowsToInsert['closed'] ?? 0),
                $closedDateValue,
                $rowsToInsert['summary'] ?? null,
                $createdAt,
                $rowsToInsert['deleted'] ?? 0,
                $rowsToInsert['editType'] ?? null
            ]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
    }

    public function ShopsAlt($dto, ?int $key = null): void {
        $pdo = getPDO();
        $pdo->beginTransaction();

        $createdAt = date('Y-m-d H:i:s');

        $rowsToAlt = [];
        $rowsToAlt = $dto->shopAltTbl[$key];
        //var_dump($rowsToAlt);
        $openDate = trim((string)($rowsToAlt['open_date'] ?? ''));
        $openDateValue = $openDate === '' ? null : $openDate;

        $closedDate = trim((string)($rowsToAlt['closed_date'] ?? ''));
        $closedDateValue = $closedDate === '' ? null : $closedDate;

        $stmt = $pdo->prepare("UPDATE shops SET
                                    user_id         = ?,
                                    shop_code       = ?,
                                    shop_name       = ?,
                                    open_date       = ?,
                                    closed          = ?,
                                    closed_date     = ?,
                                    summary         = ?,
                                    created_at      = ?,
                                    deleted         = ?,
                                    edittype        = ?
                                WHERE
                                    shop_code       = ?
        ");

        if (($rowsToAlt['editType'] ?? '') !== '更新') {
            echo "ShopRepository.ShopsAlt 論理エラー EditTypeが不正";
        }

        try {
        $stmt->execute([
            $rowsToAlt['user_id'] ?? $dto->user['id'] ?? null,
            $rowsToAlt['shop_code'] ?? null,
            $rowsToAlt['shop_name'] ?? null,
            $openDate,
            (int)($rowsToAlt['closed'] ?? 0),
            $closedDateValue,
            $rowsToAlt['summary'] ?? null,
            $rowsToAlt['created_at']?? null,
            $rowsToAlt['deleted'] ?? 0,
            $rowsToAlt['editType'] ?? null,
            $rowsToAlt['shop_code'] ?? null
        ]);
        $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
    }

}
?>