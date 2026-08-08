<?php
function getTrial($from, $to, $dto ){
	$pdo = getPDO();
    $pdoDto = new pdoDto($pdo);
    $pdo = $pdoDto->instncPdo;
	if(!$from || !$to){
		return [];
	};
	$sql = 
		"SELECT	
			a.id		as account_id,
			a.name	as name,
			a.type	as type,
			jd.side,
			SUM(jd.amount) AS total
		FROM journal_details jd
		JOIN journal_vouchers jv	ON jd.voucher_id = jv.id
		JOIN accounts a 			ON jd.account_id = a.id
		WHERE jv.voucher_date BETWEEN :from AND :to
			AND jv.user_id = :userId
			AND jv.shop_code = :shopCode
		GROUP BY a.id, a.name, a.type, jd.side
		ORDER BY a.id
		";
		$userId = $_SESSION['user']['user_id'];
		$shopCode = $dto->activeShop ?? '   all';
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':from' => $from, ':to' => $to, ':userId' => $userId, ':shopCode' => $shopCode]);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$trial = [];
	foreach ($rows as $row) {
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
?>
