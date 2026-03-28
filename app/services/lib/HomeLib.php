<?php
function StartEnd($sisan_syurui) {
	echo "資産種類；{$sisan_syurui}<br>";
	$from = ""; $to =""; $zenki_from=""; $zenki_to=""; $result="";
	$result =	[	
				'cur'   => ['from'=>null,'to'=>null],
				'prev'  => ['from'=>null,'to'=>null]	
			];
// --- 1. 年次試算表 $from, $to を再計算 ---
//echo "資産種類；{$sisan_syurui}    年次年；{$_GET['nenji_nen']}<br>";
	if ($sisan_syurui === NenjiSisanhyou && isset($_GET['nenji_nen'])) {
		echo "資産種類；{$sisan_syurui}    年次年；{$_GET['nenji_nen']}<br>";
		$from = $_GET['nenji_nen'] . '-01-01';
		$to   = $_GET['nenji_nen'] . '-12-31';
		$result['cur'] = ['from'=>$from, 'to'=>$to];
		print_r($result);
	}
// --- 2. 月次試算表 $from, $to を再計算 ---
	if ($sisan_syurui === GetujiSisanhyou && isset($_GET['from'])) {
		$from = substr($_GET['from'],0,7) . '-01';
		$to   = date('Y-m-t', strtotime($from));
		$result['cur'] = ['from'=>$from, 'to'=>$to];
	}
// --- 3. 累積試算表 $from, $to を再計算 ---ACCOUNT_START
	if ($sisan_syurui === RuisekiSisanhyou && isset($_GET['to'])) {
		$from = ACCOUNT_START;
		$to   = $_GET['to'];
		$result['cur'] = ['from'=>$from, 'to'=>$to];
	}
// --- 4. 前期比較試算表 $from, $to を再計算 ---
	if ($sisan_syurui === ZenkiHikaku && isset($_GET['kijyun_nen'])) {
		$from = $_GET['kijyun_nen'] . '-01-01';
		$to   = $_GET['kijyun_nen'] . '-12-31';
		$prev_from = ($_GET['kijyun_nen'] - 1 ) . '-01-01';
		$prev_to   = ($_GET['kijyun_nen'] - 1 ) . '-12-31';
		$result['cur']  = ['from'=>$from, 'to'=>$to];
		$result['prev'] = ['from'=>$prev_from, 'to'=>$prev_to];
	}
// --- 5. 期間入力 $from, $to を再計算 ---
	if ($sisan_syurui === KikanSisanhyou && isset($_GET['to'])) {
		$from = $_GET['from'];
		$to   = $_GET['to'];
		$result['cur'] = ['from'=>$from, 'to'=>$to];
	}
	return $result;
}
//html用表示出力用データ作成
function buildLogicalRows(array $trial): array
{
	$rows = [];
	foreach ($trial as $id => $row) {
		$rows[$id] = [
			'name'    => $row['name'],
			'type'    => $row['type'],
			'debit'   => $row['debit'],
			'credit'  => $row['credit'],
			'balance' => applyAccountingRule($row)
		];
	}

	return $rows;
}
//加算、減算　タイプ別残高計算
function applyAccountingRule($row){
	switch($row['type']){
		case '資産':
		case '費用':
			return $row['debit'] - $row['credit'];
		case '負債':
		case '収益':
		case '純資産':
			return $row['credit'] - $row['debit'];
		default:return 0;
	};
}
//DB読込集計
function getTrial($pdo,$from,$to){
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
		GROUP BY a.id, a.name, a.type, jd.side
		ORDER BY a.id
		";
		$userId = $_SETTION['user']['user_id'];
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':from' => $from, ':to' => $to, ':userId' => $userId]);
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
function getPeriodProfit(array $logicalRows): int{
	$profit = 0;
	foreach ($logicalRows as $row) {
		if ($row['type'] === '収益') {
			$profit += $row['balance'];
		}
		if ($row['type'] === '費用') {
			$profit -= $row['balance'];
		}
	}
	return $profit;
}
?>
