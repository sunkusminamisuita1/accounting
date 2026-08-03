<?php
class shopsRepository{

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
            SELECT id, user_id, shop_code, shop_name , open_date , address , closed , closed_date ,
                summary , created_at, deleted, edittype
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
        $pdo->beginTransaction();
        $CreatedAt = date('Y-m-d H:i:s');

        $RowsToInsert = [];
        $RowsToInsert = $Dto->ShopAltTbl[$Key];

        // 🛠️ デバッグ用：000002の時だけ通して、000001の時は強制終了して止める
        // if (($RowsToInsert['shop_code'] ?? '') === '000001') {
        //     echo "【デバッグ】なぜか古い店舗コード(000001)のデータでShopsAddが呼ばれました！<br>";
        //     echo "渡されたキー(Key)は: " . $Key . " です。<br>";
        //     echo "トレース情報:<br>";
        //     debug_print_backtrace(); // どこから呼び出されたかを逆引き表示
        //     exit;
        // }
        // if(  $RowsToInsert['edittype'] ){
        //     echo "<br>読み飛ばし{$RowsToInsert['shop_code']}<br>";
        //     var_dump($RowsToInsert['edittype']);
        //     exit;
        // }

        $ClosedDate = trim((string)($RowsToInsert['closed_date'] ?? ''));
        $ClosedDateValue = null;
        if ($ClosedDate !== '') {
            // 同様に "YYYYMMDD" を "YYYY-MM-DD" に変換
            if (strlen($ClosedDate) === 8) {
                $ClosedDateValue = substr($ClosedDate, 0, 4) . '-' . substr($ClosedDate, 4, 2) . '-' . substr($ClosedDate, 6, 2);
            } else {
                $ClosedDateValue = $ClosedDate;
            }
        }

        $OpenDate = trim((string)($RowsToInsert['open_date'] ?? ''));
        $OpenDateValue = null;

        if ($OpenDate !== '') {
            // "20250101" を "2025-01-01" に変換する処理を追加
            if (strlen($OpenDate) === 8) {
                $OpenDateValue = substr($OpenDate, 0, 4) . '-' . substr($OpenDate, 4, 2) . '-' . substr($OpenDate, 6, 2);
            } else {
                $OpenDateValue = $OpenDate;
            }
        }

        //var_dump($RowsToInsert);

        if (($RowsToInsert['edittype'] ?? '') !== '追加') {
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
                $RowsToInsert['user_id'] ?? $Dto->User['id'] ?? null,
                $RowsToInsert['shop_code'] ?? null,
                $RowsToInsert['shop_name'] ?? null,
                $OpenDateValue,
                (int)($RowsToInsert['closed'] ?? 0),
                $ClosedDateValue,
                $RowsToInsert['summary'] ?? null,
                $CreatedAt,
                $RowsToInsert['deleted'] ?? 0,
                $RowsToInsert['edittype'] ?? null
            ]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = $e->getMessage();
            echo $message;
            throw $e;
        }
    }

    public function ShopsAlt($Dto, ?int $Key = null): void {
        $pdo = getPDO();
        $pdo->beginTransaction();

        $CreatedAt = date('Y-m-d H:i:s');

        $RowsToAlt = [];
        $RowsToAlt = $Dto->ShopAltTbl[$Key];
        //var_dump($RowsToAlt);
        $OpenDate = trim((string)($RowsToAlt['open_date'] ?? ''));
        $OpenDateValue = $OpenDate === '' ? null : $OpenDate;

        $ClosedDate = trim((string)($RowsToAlt['closed_date'] ?? ''));
        $ClosedDateValue = $ClosedDate === '' ? null : $ClosedDate;

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

        if (($RowsToAlt['edittype'] ?? '') !== '更新') {
            echo "ShopRepository.ShopsAlt 論理エラー EditTypeが不正";
        }

        try {
        $stmt->execute([
            $RowsToAlt['user_id'] ?? $Dto->User['id'] ?? null,
            $RowsToAlt['shop_code'] ?? null,
            $RowsToAlt['shop_name'] ?? null,
            $OpenDate,
            (int)($RowsToAlt['closed'] ?? 0),
            $ClosedDateValue,
            $RowsToAlt['summary'] ?? null,
            $RowsToAlt['created_at']?? null,
            $RowsToAlt['deleted'] ?? 0,
            $RowsToAlt['edittype'] ?? null,
            $RowsToAlt['shop_code'] ?? null
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