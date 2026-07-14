<?php
class ShopsRepository
{

    public function getShopsByUserId($Dto): array
    {
        $pdo = getPDO();
//| id | user_id | shop_code | shop_name    | open_date | address | closed | closed_date | summary | created_at          |

        $stmt = $pdo->prepare("
            SELECT id, shop_code, shop_name , open_date , address , closed , closed_date , summary
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
