<?php
class HomeServiceCls{
    public $result;
	public $ReportType;
	public $from;
	public $to;
	public $zenki_from;
	public $zenki_to;

    public function __construct($ReportType) {
        $this->ReportType = $ReportType;
        $this->result = [];
		$this->from = "";
		$this->to = "";
		$this->zenki_from = "";
		$this->zenki_to = "";

	}
	public function HomeService(){
		echo "homeservice1";
		require_once ROOT_PATH . '/app/DTO/Constants.php';
		require_once ROOT_PATH . '/app/services/lib/HomeLib.php';
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
// --- 1. 入力値の受け取り  ---
//		$data				=	StartEnd($this->ReportType);
//		$this->from			=	$data['cur']['from']??"";
//		$this->to			=	$data['cur']['to']??"";
//		$this->zenki_from	=	$data['prev']['from']??"";
//		$this->zenki_to		=	$data['prev']['to']??"";
//	print_r($data);
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$pdo = getPDO();
// --- 1. 入力値の受け取り  ---
			$data				=	StartEnd($this->ReportType);
			echo "homeservice2:{$this->ReportType}<br>";
			print_r($data);
			$this->from			=	$data['cur']['from']??"";
			$this->to			=	$data['cur']['to']??"";
			$this->zenki_from	=	$data['prev']['from']??"";
			$this->zenki_to		=	$data['prev']['to']??"";
//	print_r($data);
//対象データ読込
			echo "homeservice3:from=";	
			$trial_cur		= 	getTrial($pdo,$this->from,$this->to);
			echo "homeservice4:to=";
			$trial_cur_bs	= 	getTrial($pdo, ACCOUNT_START, $this->to);
			if ($this->zenki_from && $this->zenki_to) {
				$trial_prev		= getTrial($pdo,$this->zenki_from,$this->zenki_to);
				$trial_prev_bs	= getTrial($pdo, ACCOUNT_START, $this->zenki_to);
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
			switch($this->ReportType){
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
					$prev_rows    = buildLogicalRows($trial_prev_bs);
					$prev_capital = getPeriodProfit($prev_rows);
					$cur_rows     = buildLogicalRows($trial_cur_bs);
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
				case ZenkiHikaku:
		//前期比較集計処理   使用データ　ーー＞　$trial_cur_bs $trial_prev_bs	
					$bs_compare = [];
					$ini = [
							'name'    => null,
							'type'    => null,
							'balance' => 0
							];
					$cur_rows		=	buildLogicalRows($trial_cur_bs);
					$prev_rows		=	buildLogicalRows($trial_prev_bs);
					$Ids			=	array_unique(array_merge(
										array_keys($cur_rows),
										array_keys($prev_rows)
									));
					foreach ($Ids as $id) {
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
}