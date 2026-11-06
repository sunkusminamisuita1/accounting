<?php
function startEnd($sisan_syurui, $reportType = null) {
	$from = ""; $to =""; $result="";
	$result =	[	
				'cur'   => ['from'=>null,'to'=>null],
				'prev'  => ['from'=>null,'to'=>null]	
			];
// --- 1. 年次試算表 $from, $to を再計算 ---
	if (($sisan_syurui === nenjiSisanhyou || $sisan_syurui === '年次') && isset($_POST['nenji_nen'])) {
		$FiscalPeriod = getFiscalPeriod($_POST['nenji_nen'], 
										$_SESSION['user']['fiscal_month'], $_SESSION['user']['fiscal_day']);
		$from = $FiscalPeriod['fromDate'];
		$to   = $FiscalPeriod['toDate'];
		// $from = $_POST['nenji_nen'] . '-01-01';
		// $to   = $_POST['nenji_nen'] . '-12-31';
		$result['cur'] = ['from'=>$from, 'to'=>$to];
	}
// --- 2. 月次試算表 $from, $to を再計算 ---
	if (($sisan_syurui === getujiSisanhyou || $sisan_syurui === '月次') && isset($_POST['from'])) {
		$from = substr($_POST['from'],0,7) . '-01';
		$to   = date('Y-m-t', strtotime($from));
		$result['cur'] = ['from'=>$from, 'to'=>$to];
	}
// --- 3. 累積試算表 $from, $to を再計算 ---ACCOUNT_START
	if (($sisan_syurui === ruisekiSisanhyou || $sisan_syurui === '累積') && isset($_POST['to'])) {
		$from = ACCOUNT_START;
		$to   = $_POST['to'];
		$result['cur'] = ['from'=>$from, 'to'=>$to];
	}
// --- 4. 前期比較試算表 $from, $to を再計算 ---
	if (($sisan_syurui === zenkiHikaku || $sisan_syurui === '前期比較') && isset($_POST['kijyun_nen'])) {
		$FiscalPeriod = getFiscalPeriod($_POST['kijyun_nen'], 
										$_SESSION['user']['fiscal_month'], $_SESSION['user']['fiscal_day']);
		$from = $FiscalPeriod['fromDate'];
		$to   = $FiscalPeriod['toDate'];
		// $from = $_POST['kijyun_nen'] . '-01-01';
		// $to   = $_POST['kijyun_nen'] . '-12-31';
		$FiscalPeriod = getFiscalPeriod($_POST['kijyun_nen'] - 1, 
										$_SESSION['user']['fiscal_month'], $_SESSION['user']['fiscal_day']);
		$prev_from = $FiscalPeriod['fromDate'];
		$prev_to   = $FiscalPeriod['toDate'];
		// $prev_from = ($_POST['kijyun_nen'] - 1 ) . '-01-01';
		// $prev_to   = ($_POST['kijyun_nen'] - 1 ) . '-12-31';
		$result['cur']  = ['from'=>$from, 'to'=>$to];
		$result['prev'] = ['from'=>$prev_from, 'to'=>$prev_to];
	}
// --- 5. 期間入力 $from, $to を再計算 ---
	if (($sisan_syurui === kikanSisanhyou || $sisan_syurui === '期間') && isset($_POST['from'], $_POST['to'])) {
		$from = $_POST['from'];
		$to   = $_POST['to'];
		$result['cur'] = ['from'=>$from, 'to'=>$to];
	}
	$result['reportType'] = $reportType ?? $sisan_syurui;
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

function dbg($hairetuMei,$hairetu){
	 $trace = debug_backtrace();

    // 呼び出し元のファイル名
    $fileName = $trace[0]['file'] ?? '';

    // 呼び出し元の関数名
    $functionName = $trace[0]['function'] ?? '';

    echo "<br>呼び出し元ファイル名: " . $fileName . "<br>";
    echo "呼び出し元関数名: " . $functionName;

	echo "<br>" . $hairetuMei . "<br><pre>";
	var_dump($hairetu);
	echo "</pre>";
}

/**
 * 決算期間を取得する
 *
 * @param int $year       決算終了年
 * @param int $fiscalMonth 決算月
 * @param int $fiscalDay   決算日
 * @return array
 */
function getFiscalPeriod($year, $fiscalMonth, $fiscalDay)
{
    if (
        !checkdate($fiscalMonth, $fiscalDay, $year)
    ) {
        throw new InvalidArgumentException('決算日が不正です。');
    }

    // 決算終了日
    $endDate = new DateTimeImmutable(
        sprintf('%04d-%02d-%02d', $year, $fiscalMonth, $fiscalDay)
    );

    // 前年度の決算日を求める
    $prevYear = $year - 1;

    // 前年度の同じ月の末日を取得
    $prevMonth = new DateTimeImmutable(
        sprintf('%04d-%02d-01', $prevYear, $fiscalMonth)
    );

    // うるう年などで前年度に同じ日が存在しない場合は月末に調整
    $prevDay = min(
        $fiscalDay,
        (int)$prevMonth->format('t')
    );

    $prevEndDate = $prevMonth->setDate(
        $prevYear,
        $fiscalMonth,
        $prevDay
    );

    // 前年度の決算日の翌日が当年度の開始日
    $startDate = $prevEndDate->modify('+1 day');

    return [
        'fromDate' => $startDate->format('Y-m-d'),
        'toDate'   => $endDate->format('Y-m-d')
    ];
}

?>
