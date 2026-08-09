<?php
require_once ROOT_PATH . '/app/repositories/homeRepository.php';
class homeServiceCls{
    public $result;
	public $reportType;
	public $from;
	public $to;
	public $zenki_from;
	public $zenki_to;
	public $repo;

    public function __construct($reportType) {
        $this->reportType = $reportType;
        $this->result = [];
		$this->from = "";
		$this->to = "";
		$this->zenki_from = "";
		$this->zenki_to = "";
		$this->repo = new homeRepository();

            // $dto->reportType = $reportType;
            // $dto->from = $_POST['from'] ?? "";
            // $dto->to = $_POST['to'] ?? "";
            // $dto->kijyun_nen = $_POST['kijyun_nen'] ?? "";
            // $dto->nenji_nen = $_POST['nenji_nen'] ?? "";

	}
	public function homeService(homeDto $dto){
		require_once ROOT_PATH . '/app/dto/constants.php';
		require_once ROOT_PATH . '/app/services/lib/homeLib.php';
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		if (!defined('ROOT_PATH')) {
			exit('Direct access not allowed');
		}
		$displayOrder = [
				'資産'     => 1, '負債'     => 2, '純資産'   => 3,
				'収益'     => 4, '費用'     => 5,
		];
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$pdo = getPDO();
			//var_dump($dto->post);
		// --- 1. 入力値の受け取り  ---
			$data				=	$this->startEnd($this->reportType);
			$this->from			=	$data['cur']['from']??"";
			$this->to			=	$data['cur']['to']??"";
			$this->zenki_from	=	$data['prev']['from']??"";
			$this->zenki_to		=	$data['prev']['to']??"";
		//対象データ読込
			$x = ACCOUNT_START;
			echo "<br>xxxxxxxxxxxx{$x}xxxxxxxxxxxx<br>";
			$trial_cur		= 	$this->repo->getTrial($pdo,$this->from,$this->to,$dto);
			$trial_cur_bs	= 	$this->repo->getTrial($pdo, ACCOUNT_START, $this->to ,$dto);
			if ($this->zenki_from && $this->zenki_to) {
				$trial_prev		= $this->repo->getTrial($pdo,$this->zenki_from,$this->zenki_to, $dto);
				$trial_prev_bs	= $this->repo->getTrial($pdo, ACCOUNT_START, $this->zenki_to, $dto);
			}else{
				$trial_prev	= [];
				$trial_prev_bs	= [];
			}
		//科目コード一覧(全件)
			$account_codes = array_merge(
				array_keys($trial_cur),
				array_keys($trial_prev),
				array_keys($trial_cur_bs),
				array_keys($trial_prev_bs)
			);
		//----------集計処理-----------
			switch($this->reportType){
				case getujiSisanhyou:
				case nenjiSisanhyou:
				case kikanSisanhyou:
					$total = [
						'row_type'	=> 'total',
						'name'		=> "",
						'label'		=> '合計',
						'debit'		=> 0,
						'credit'		=> 0
					];
		//月次・年次・期間 試算表集計
					$rows = $this->buildLogicalRows($trial_cur);
					foreach ($rows as $id => $row) {
						$total['debit']  += $row['debit'];
						$total['credit'] += $row['credit'];
						$this->result[$id] = [
							'row_type' => 'account',
							'name'    => $row['name'],
							'type'    => $row['type'],
							'debit'   => $row['debit'],
							'credit'  => $row['credit'],
							'balance' => $row['balance']
						];
					}
					$this->result[] = $total;
					break;
		//累積試算表集計      in_array($type, PL_TYPE, true)
				case ruisekiSisanhyou:
					$logical_rows = $this->buildLogicalRows($trial_cur_bs);
		// 集計箱
					$totals = [
						'資産'   => 0,
						'負債'   => 0,
						'純資産' => 0,
						'収益'   => 0,
						'費用'   => 0,
					];
					$this->result = [];
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
							$this->result[] = [
								'row_type' => 'account',
								'label'    => '',
								'name'     => $row['name'],
								'type'     => $type,
								'balance'  => $balance
							];
						}	
					}
					$prev_rows    = $this->buildLogicalRows($trial_prev_bs);
					$prev_capital = getPeriodProfit($prev_rows);
					$cur_rows     = $this->buildLogicalRows($trial_cur_bs);
					$cur_capital  = getPeriodProfit($cur_rows);
		// 表示行
					$this->result[] = [
						'row_type' => 'account',
						'label'    => '',
						'name'     => '前期迄資本増加額',
						'type'     => '純資産',
						'balance'  => $prev_capital
					];
					$this->result[] = [
						'row_type' => 'account',
						'label'    => '',
						'name'     => '当期資本増加額',
						'type'     => '純資産',
						'balance'  => $cur_capital
					];
					$totals['純資産'] += ($prev_capital + $cur_capital);
		// 小計行
					foreach (['資産','負債','純資産'] as $type) {
						$this->result[] = [
							'row_type' => 'subtotal',
							'label'    => $type.' 小計',
							'name'     => '',
							'type'     => '',
							'balance'  => $totals[$type]
						];
					}
		// 検算
					$this->result[] = [
						'row_type' => 'subtotal',
						'label'    => '検算（資産−負債−純資産）',
						'name'     => '',
						'type'     => '',
						'balance'  => $totals['資産'] - ($totals['負債'] + $totals['純資産'])
					];
					usort($this->result, function ($a, $b) use ($displayOrder) {
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
				case zenkiHikaku:
		//前期比較集計処理   使用データ　ーー＞　$trial_cur_bs $trial_prev_bs	
					$bs_compare = [];
					$ini = [
							'name'    => null,
							'type'    => null,
							'balance' => 0
							];
					$cur_rows		=	$this->buildLogicalRows($trial_cur_bs);
					$prev_rows		=	$this->buildLogicalRows($trial_prev_bs);
					$ids			=	array_unique(array_merge(
										array_keys($cur_rows),
										array_keys($prev_rows)
									));
					foreach ($ids as $id) {
						$cur  = $cur_rows[$id]  ?? $ini;
						$prev = $prev_rows[$id] ?? $ini;
						$name = $cur['name'] ?? $prev['name'];
						$type = $cur['type'] ?? $prev['type'];
						$this->result[$id] = [
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
	}

	function startEnd($sisan_syurui) {
		$from = ""; $to =""; $zenki_from=""; $zenki_to=""; $result="";
		$result =	[	
					'cur'   => ['from'=>null,'to'=>null],
					'prev'  => ['from'=>null,'to'=>null]	
				];
	// --- 1. 年次試算表 $from, $to を再計算 ---
		if ($sisan_syurui === nenjiSisanhyou && isset($_POST['nenji_nen'])) {
			$from = $_POST['nenji_nen'] . '-01-01';
			$to   = $_POST['nenji_nen'] . '-12-31';
			$result['cur'] = ['from'=>$from, 'to'=>$to];
		}
	// --- 2. 月次試算表 $from, $to を再計算 ---
		if ($sisan_syurui === getujiSisanhyou && isset($_POST['from'])) {
			$from = substr($_POST['from'],0,7) . '-01';
			$to   = date('Y-m-t', strtotime($from));
			$result['cur'] = ['from'=>$from, 'to'=>$to];
		}
	// --- 3. 累積試算表 $from, $to を再計算 ---ACCOUNT_START
		if ($sisan_syurui === ruisekiSisanhyou && isset($_POST['to'])) {
			$from = ACCOUNT_START;
			$to   = $_POST['to'];
			$result['cur'] = ['from'=>$from, 'to'=>$to];
		}
	// --- 4. 前期比較試算表 $from, $to を再計算 ---
		if ($sisan_syurui === zenkiHikaku && isset($_POST['kijyun_nen'])) {
			$from = $_POST['kijyun_nen'] . '-01-01';
			$to   = $_POST['kijyun_nen'] . '-12-31';
			$prev_from = ($_POST['kijyun_nen'] - 1 ) . '-01-01';
			$prev_to   = ($_POST['kijyun_nen'] - 1 ) . '-12-31';
			$result['cur']  = ['from'=>$from, 'to'=>$to];
			$result['prev'] = ['from'=>$prev_from, 'to'=>$prev_to];
		}
	// --- 5. 期間入力 $from, $to を再計算 ---
		if ($sisan_syurui === kikanSisanhyou && isset($_POST['to'])) {
			$from = $_POST['from'];
			$to   = $_POST['to'];
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
				'balance' => $this->applyAccountingRule($row)
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
}