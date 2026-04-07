<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!defined('ROOT_PATH')) {
	exit('Direct access not allowed');
}
$sisan_syurui = $_POST['sisan_syurui'] ?? '月次試算表';
$result = [];
$from = $_POST['from'] ?? '';			$to   = $_POST['to']   ?? '';
define('ACCOUNT_START', '2020-01-01');	define('BS_TYPE', ['資産','負債','純資産']);
define('PL_TYPE', ['収益','費用']);		define('RuisekiSisanhyou', '累積試算表');
define('GetujiSisanhyou', '月次試算表');	define('NenjiSisanhyou', '年次試算表');
define('KikanSisanhyou', '期間入力');	define('ZenkiHikaku', '前期比較');
$displayOrder = [
			'資産'     => 1,
			'負債'     => 2,
			'純資産'   => 3,
			'収益'     => 4,
			'費用'     => 5,
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$pdo = getPDO();
//	$sisan_syurui = $_GET['sisan_syurui'] ?? '月次試算表';
//	$result = [];
//	$from = $_GET['from'] ?? '';			$to   = $_GET['to']   ?? '';
//	define('ACCOUNT_START', '2020-01-01');	define('BS_TYPE', ['資産','負債','純資産']);
//	define('PL_TYPE', ['収益','費用']);		define('RuisekiSisanhyou', '累積試算表');
//	define('GetujiSisanhyou', '月次試算表');	define('NenjiSisanhyou', '年次試算表');
//	define('KikanSisanhyou', '期間入力');	define('ZenkiHikaku', '前期比較');
//	$displayOrder = [
//	    '資産'     => 1,
//	    '負債'     => 2,
//	    '純資産'   => 3,
//	    '収益'     => 4,
//	    '費用'     => 5,
//	];
// --- 1. 入力値の受け取り  ---
	$data		=	calcPeriod($sisan_syurui);
	$from		=	$data['cur']['from']??"";
	$to			=	$data['cur']['to']??"";
	$zenki_from	=	$data['prev']['from']??"";
	$zenki_to		=	$data['prev']['to']??"";
//対象データ読込
	$trial_cur		= 	getTrial($pdo,$from,$to);
	$trial_cur_bs	= 	getTrial($pdo, ACCOUNT_START, $to);
	if ($zenki_from && $zenki_to) {
		$trial_prev		= getTrial($pdo,$zenki_from,$zenki_to);
		$trial_prev_bs	= getTrial($pdo, ACCOUNT_START, $zenki_to);
	}else{
		$trial_prev	= [];
		$trial_prev_bs	= [];
	}
//科目コード一覧(全件)
	array_merge(
		array_keys($trial_cur),
		array_keys($trial_prev),
		array_keys($trial_cur_bs),
		array_keys($trial_prev_bs)
	);
//----------集計処理-----------
	switch($sisan_syurui){
		case GetujiSisanhyou:
		case NenjiSisanhyou:
		case KikanSisanhyou:
			$total = [
				'row_type'	=> 'total',
				'name'		=> "",
				'label'		=> '合計',
				'debit'		=> 0,
				'credit'		=> 0
			];
		//月次・年次・期間 試算表集計
			$rows = buildLogicalRows($trial_cur);
			foreach ($rows as $id => $row) {
				$total['debit']  += $row['debit'];
				$total['credit'] += $row['credit'];
				$result[$id] = [
					'row_type' => 'account',
					'name'    => $row['name'],
					'type'    => $row['type'],
					'debit'   => $row['debit'],
					'credit'  => $row['credit'],
					'balance' => $row['balance']
				];
			}
			$result[] = $total;
			break;
		//累積試算表集計      in_array($type, PL_TYPE, true)
		case RuisekiSisanhyou:
			$logical_rows = buildLogicalRows($trial_cur_bs);
		// 集計箱
			$totals = [
				'資産'   => 0,
				'負債'   => 0,
				'純資産' => 0,
				'収益'   => 0,
				'費用'   => 0,
			];
			$result = [];
	// 科目行の構築
			foreach ($logical_rows as $id => $row) {
				$type    = $row['type'];
				$balance = $row['balance'];
		// タイプ別合計
				if (isset($totals[$type])) {
					$totals[$type] += $balance;
			}
		// BS科目だけ表示対象
				if (in_array($type, ['資産','負債','純資産'], true)) {
					$result[] = [
						'row_type' => 'account',
						'label'    => '',
						'name'     => $row['name'],
						'type'     => $type,
						'balance'  => $balance
					];
				}	
			}
			$prev_rows    = buildLogicalRows($trial_prev_bs);
			$prev_capital = getPeriodProfit($prev_rows);
			$cur_rows     = buildLogicalRows($trial_cur_bs);
			$cur_capital  = getPeriodProfit($cur_rows);
// 表示行
			$result[] = [
				'row_type' => 'account',
				'label'    => '',
				'name'     => '前期迄資本増加額',
				'type'     => '純資産',
				'balance'  => $prev_capital
			];
			$result[] = [
				'row_type' => 'account',
				'label'    => '',
				'name'     => '当期資本増加額',
				'type'     => '純資産',
				'balance'  => $cur_capital
			];
			$totals['純資産'] += ($prev_capital + $cur_capital);
	// 小計行
			foreach (['資産','負債','純資産'] as $type) {
				$result[] = [
					'row_type' => 'subtotal',
					'label'    => $type.' 小計',
					'name'     => '',
					'type'     => '',
					'balance'  => $totals[$type]
				];
			}
	// 検算
			$result[] = [
				'row_type' => 'subtotal',
				'label'    => '検算（資産−負債−純資産）',
				'name'     => '',
				'type'     => '',
				'balance'  => $totals['資産'] - ($totals['負債'] + $totals['純資産'])
			];
			usort($result, function ($a, $b) use ($displayOrder) {
	// 小計は必ず後ろ
				if ($a['row_type'] === 'subtotal' && $b['row_type'] !== 'subtotal')
							return 1;
				if ($a['row_type'] !== 'subtotal' && $b['row_type'] === 'subtotal')
							return -1;
	// 科目同士：type順
				$orderA = $displayOrder[$a['type']] ?? 99;
				$orderB = $displayOrder[$b['type']] ?? 99;
				if ($orderA !== $orderB) {
					return $orderA <=> $orderB;
				}
	// 同じタイプ内は名前順
				return strcmp($a['name'], $b['name']);
			});
			break;
		case ZenkiHikaku:
		//前期比較集計処理   使用データ　ーー＞　$trial_cur_bs $trial_prev_bs	
			$bs_compare = [];
			$ini = [
					'name'    => null,
					'type'    => null,
					'balance' => 0
			];
			$cur_rows		=	buildLogicalRows($trial_cur_bs);
			$prev_rows	=	buildLogicalRows($trial_prev_bs);
			$Ids			=	array_unique(array_merge(
								array_keys($cur_rows),
								array_keys($prev_rows)
							));
			foreach ($Ids as $id) {
				$cur  = $cur_rows[$id]  ?? $ini;
				$prev = $prev_rows[$id] ?? $ini;
				$name = $cur['name'] ?? $prev['name'];
				$type = $cur['type'] ?? $prev['type'];
				$result[$id] = [
					'name'         => $name,
					'type'         => $type,
					'cur_balance'  => $cur['balance'],
					'prev_balance' => $prev['balance'],
					'diff'         => $cur['balance'] - $prev['balance']
				];
			}
			break;
	}
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
function calcPeriod($sisan_syurui) {
	$from = ""; $to =""; $zenki_from=""; $zenki_to=""; $result="";
	$result =	[	
				'cur'   => ['from'=>null,'to'=>null],
				'prev'  => ['from'=>null,'to'=>null]	
			];
// --- 1. 年次試算表 $from, $to を再計算 ---
	if ($sisan_syurui === NenjiSisanhyou && isset($_GET['nenji_nen'])) {
		$from = $_GET['nenji_nen'] . '-01-01';
		$to   = $_GET['nenji_nen'] . '-12-31';
		$result['cur'] = ['from'=>$from, 'to'=>$to];
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
//PL BS 判定
function isPL($type) {
	return in_array($type, PL_TYPE, true);
}
?>
<!DOCTYPE html>
<html lang="ja">
	<head>
		<meta charset="UTF-8">
		<title>試算表テスト</title>
		<style>
			table { border-collapse: collapse; width: 100%; }
			th { border: 1px solid #ccc; padding: 8px; text-align: right;
				background: #f4f4f4; text-align: center; }
			th { background: #f4f4f4; text-align: center; }
			td { border: 1px solid #ccc; padding: 8px; text-align: right; }
			.text-left { text-align: left; }
		</style>
	</head>
	<body>
	<h1>ホーム画面</h1>
	<p>ようこそ <?= htmlspecialchars($_SESSION['user']['username'] ?? 'ゲスト') ?></p>
	<form action="index.php?route=logout" method="post">
		<input type="hidden" name="csrfToken" value="<?= h(generateCsrfToken()) ?>">
		<button type="submit">ログアウト</button>
	</form>
		<h2>試算表表示：<?= $sisan_syurui ?></h2>		
		<form action="index.php?route=home" method="post">
			試算表<br>
			<input type="radio" name="sisan_syurui" 
					value=<?= '"'. GetujiSisanhyou .'"'?>>月次試算表出力
			<input type="radio" name="sisan_syurui" 
					value=<?= '"'. NenjiSisanhyou .'"'?>>年次試算表出力
			<input type="radio" name="sisan_syurui" 
					value=<?= '"'. RuisekiSisanhyou .'"'?>>累積試算表出力
			<input type="radio" name="sisan_syurui" 
					value=<?= '"'. ZenkiHikaku .'"'?>>前期比較出力
			<input type="radio" name="sisan_syurui" 
					value=<?= '"'. KikanSisanhyou .'"'?>>期間入力試算表出力
			<br>
			<button type="submit">切替</button><br><br>
		</form>
		<form method="post" action="index.php?route=home">
		<input type="hidden" name="sisan_syurui"
   				value="<?= h($sisan_syurui ?? '') ?>"> 
<?php
	$today = new DateTime();
	$nenji_nen = $nenji_nen ?? "";
	$lastDate = $today->modify('-1 month');
	if ($sisan_syurui) {
		if($sisan_syurui === GetujiSisanhyou):
?>
			年月：<input type="month" name="from"
			value="<?= h($from) ?>" required>
		<?php endif;
		if($sisan_syurui === NenjiSisanhyou):
		?>		
			年：<input type="number" name="nenji_nen" min='1900' max='2100'
				value="<?= h($nenji_nen) ?>" required placeholder="例: 2025">
		<?php
			$from = isset($_GET['nenji_nen'])?$_GET['nenji_nen'] . '0101':"";
		endif;
		if($sisan_syurui === RuisekiSisanhyou):
		?>
			試算表期日：<input type="date" name="to" value="<?= h($to) ?>" required>	
		<?php endif;
		if($sisan_syurui === ZenkiHikaku):
		?>
			基準年：<input type="number" name="kijyun_nen" min='1900' max='2100'
				value="" required placeholder="例: 2025">
		<?php endif;
		if($sisan_syurui === KikanSisanhyou){
		?>
			開始日：<input type="date" name="from" value="<?= h($from) ?>" required>
			終了日：<input type="date" name="to" value="" required>
		<?php
		};
	}		
	?>
			<br>
			<button type="submit">計算実行</button>
		</form>
		<?php if (in_array($sisan_syurui, [GetujiSisanhyou, NenjiSisanhyou, KikanSisanhyou])): ?>
<p>抽出期間： <?= h($from) ?> 〜 <?= h($to) ?></p>
<table>
	<thead>
		<tr>
			<th>科目</th>
			<th>借方</th>
			<th>貸方</th>
			<th>残高</th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($result as $row): ?>
	<?php if ($row['row_type'] === 'account'): ?>
		<tr>
			<td class="text-left"><?= h($row['name']) ?></td>
			<td><?= number_format($row['debit']) ?></td>
			<td><?= number_format($row['credit']) ?></td>
			<td><?= number_format($row['balance']) ?></td>
		</tr>
	<?php elseif ($row['row_type'] === 'total'): ?>
		<tr>
			<th><?= h($row['label']) ?></th>
			<th><?= number_format($row['debit']) ?></th>
			<th><?= number_format($row['credit']) ?></th>
			<th></th>
		</tr>
	<?php endif; ?>
<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
<?php if (in_array($sisan_syurui,[RuisekiSisanhyou])): ?>
<p>期間： <?= h($from) ?> 〜 <?= h($to) ?></p>
<table>
	<thead>
	<tr>
		<th>科目</th>
		<th>残高</th>
	</tr>
	</thead>
	<tbody>
<?php foreach ($result as $row): ?>
	<?php if ($row['row_type'] === 'account'): ?>
	<tr>
		<td class="text-left"><?= h($row['name']) ?></td>
		<td><?= number_format($row['balance']) ?></td>
	</tr>
	<?php elseif ($row['row_type'] === 'subtotal'): ?>	<tr>
		<th class="text-left"><?= h($row['label']) ?></th>
		<th><?= number_format($row['balance']) ?></th>
	</tr>

	<?php elseif ($row['row_type'] === 'total'): ?>
	<tr style="background:#eee;">
		<th class="text-left"><?= h($row['label']) ?></th>
		<th><?= number_format($row['balance']) ?></th>
	</tr>
	<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
	</tbody>
</table>
<?php if (in_array($sisan_syurui,[ZenkiHikaku])): ?>
<p>当期期間： <?= h($from) ?> 〜 <?= h($to) ?></p>
<p>前期期間： <?= h($zenki_from) ?> 〜 <?= h($zenki_to) ?></p>
<table>
	<thead>
		<tr>
			<th>科目</th>
			<th>当期残高</th>
			<th>前期残高</th>
			<th>増減</th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($result as $row): ?>
		<tr>
			<td class="text-left"><?= h($row['name']) ?></td>
			<td><?= number_format($row['cur_balance']) ?></td>
			<td><?= number_format($row['prev_balance']) ?></td>
			<td><?= number_format($row['diff']) ?></td>
		</tr>
<?php endforeach; ?>
<?php endif; ?>
	</tbody>
</table>
