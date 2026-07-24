<?php
class ShopsRepository
{

    public function getShopsByUserId($Dto): array
    {
        $pdo = getPDO();
        //echo "userid=" . var_dump($Dto->User['id']);exit;
//| id | user_id | shop_code | shop_name    | open_date | address | closed | closed_date | summary | created_at  | edittype

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
}
?>
