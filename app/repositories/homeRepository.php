<?php
class homeRepository{

	function getTrial($pdo, $from, $to, $dto ){
		//echo "aaaaaaaaaaaaaa";exit;
		//$pdo = getPDO();
		//$pdoDto = new pdoDto($pdo);
		//$pdo = $pdoDto->instncPdo;
		if(!$from || !$to){
			return [];
		};
		$sql0 = 
			"SELECT	
				a.id		as account_id,
				a.name	as name,
				a.type	as type,
				jd.side,
				SUM(jd.amount) AS total
			FROM journal_details jd
			JOIN journal_vouchers jv	ON jd.voucher_id = jv.id
			JOIN accounts a 			ON jd.account_id = a.id
			";
		$sql1 =	
			"WHERE jv.voucher_date BETWEEN :from AND :to
				AND jv.user_id = :userId
			";
		$sql2 =	
			"WHERE jv.voucher_date BETWEEN :from AND :to
				AND jv.user_id = :userId
				AND jv.shop_code = :shopCode
			";
		$sql3 =
			"GROUP BY a.id, a.name, a.type, jd.side
			ORDER BY a.id
			";
			//$userId = $_SESSION['user']['user_id'];  ############################   $dto->activeShop
			$userId = $_SESSION['user']['id'];
			$shopCode = $dto->session['currentShopCode'] ?? '   all';

			if($shopCode !== '   all'){
				$sql = $sql0 . $sql2 . $sql3 ;
			}else{
			//shopCode '   all'連結決算の場合
				$sql = $sql0 . $sql1 . $sql3 ;
			}

			$stmt = $pdo->prepare($sql);

			if($shopCode !== '   all'){
				$stmt->execute( [ ':from' => $from, ':to' => $to, ':userId' => $userId, ':shopCode' => $shopCode ] );
			}else{
			//shopCode '   all'連結決算の場合
				$stmt->execute( [ ':from' => $from, ':to' => $to, ':userId' => $userId ] );
			}

			
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			echo "<br>入力ショップコード: " . var_dump($shopCode) . "<br>";
		$trial = [];
		foreach ($rows as $row) {
			//echo "<br>homeRepository.getTrial  journal_vouchers: " . var_dump($row) . "<br>";exit;

			$id = $row['account_id'];
			if (!isset($trial[$id])) {
				$trial[$id] = ['name'	=> $row['name'],
							'type'	=> $row['type'],
							'debit'	=> 0,
							'credit'	=> 0
				];
			}
			$trial[$id][$row['side']] += $row['total'];
		}
		return $trial;
	}
}
?>
