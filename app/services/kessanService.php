<?php
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
require_once ROOT_PATH . '/app/repositories/kessanRepository.php';
require_once ROOT_PATH . '/app/repositories/accountsRepository.php';
require_once ROOT_PATH . '/app/services/lib/homeLib.php';
require_once ROOT_PATH . '/lib/helpers.php';

class kessanService{
    public $result;
	public $reportType;
	public $from;
	public $to;
	public $zenki_from;
	public $zenki_to;
	public $repo;
	public $accRepo;
	private $dto;
	private $pdo;
	private $accDto;

    public function __construct($dto, $pdo) {
		$this->dto	= $dto;	
		$this->accDto = new accountsDto();
		$this->pdo	= $pdo;
        $this->reportType = $this->dto->reportType;
        $this->result = [];
		$this->from = "";
		$this->to = "";
		$this->zenki_from = "";
		$this->zenki_to = "";
		$this->repo = new kessanRepository($this->dto, $this->pdo);
		$this->accRepo = new accountsRepository($this->accDto, $this->pdo);
	}

	public function keihiItiran(){

		$this->reportType = $this->dto->reportType;

		$reportSubType = $this->dto->reportSubType ?? '';
		if (!in_array($reportSubType, ['月次', '年次', '累積', '前期比較', '期間'], true)) {
			$this->dto->errmsg = '無効なレポートタイプです。';
			return 1;
		}

		$this->syuukei();
		$this->dto->keihiItiran = array_values($this->dto->keihiItiran);
		return $this->dto->keihiItiran;
	}

	private function syuukei(){

		$reportSubType = $this->dto->reportSubType ?? '';
		$period = startEnd($reportSubType, $this->dto->reportType);
		$this->dto->from       = $period['cur']['from'] ?? '';
		$this->dto->to         = $period['cur']['to'] ?? '';
		$this->dto->zenki_from = $period['prev']['from'] ?? '';
		$this->dto->zenki_to   = $period['prev']['to'] ?? '';

		$keihiData = $this->repo->getGetujiKeihiData();

		$this->accDto->shopCode = $this->dto->session['currentShopCode']?? '   all';
		$accounts = $this->accRepo->getAccounts(true);

		$this->keihiDataSyuukei($keihiData);
		// foreach ($keihiData as $key => $row) {
		// 	if ($row['type'] !== '費用' || !in_array($row['side'], ['debit', 'credit'], true)) {
		// 			continue;
		// 	}

		// 	if( !isset($this->dto->keihiItiran[$row['id']]) ){
		// 		$this->dto->keihiItiran[ $row['id'] ] =
		// 			[
		// 				'keihiAccountName'        => $row['name'],
		// 				'keihiAmountTotal'        => 0,
		// 				'zKeihiAmountTotal'       => 0,
		// 			];
		// 	}

		// 	$this->dto->keihiItiran[$row['id']]['keihiAmountTotal'] +=
		// 		($row['side'] === 'debit') ? $row['total'] : -$row['total'];
		// }

		if ($this->dto->reportSubType === '前期比較'){
			
			$this->dto->from = $period['prev']['from'] ?? '';
			$this->dto->to   = $period['prev']['to'] ?? '';
			$keihiData = $this->repo->getGetujiKeihiData();
			$this->dto->from = $period['cur']['from'] ?? '';
			$this->dto->to   = $period['cur']['to'] ?? '';

			$this->keihiDataSyuukei($keihiData);
			// foreach ($keihiData as $key => $row) {
			// 	if ($row['type'] !== '費用' || !in_array($row['side'], ['debit', 'credit'], true)) {
			// 			continue;
			// 	}

			// 	if( !isset($this->dto->keihiItiran[$row['id']]) ){
			// 		$this->dto->keihiItiran[ $row['id'] ] =
			// 			[
			// 				'keihiAccountName'   => $row['name'],
			// 				'keihiAmountTotal'   => 0,
			// 				'zKeihiAmountTotal'  => 0,
			// 			];
			// 	}
			// 	$this->dto->keihiItiran[$row['id']]['zKeihiAmountTotal'] +=
			// 		($row['side'] === 'debit') ? $row['total'] : -$row['total'];
			// }
		}


		return $this->dto->keihiItiran;
	}

	//経費データ集計
	function keihiDataSyuukei($keihiData){
		foreach ($keihiData as $key => $row) {
			if ($row['type'] !== '費用' || !in_array($row['side'], ['debit', 'credit'], true)) {
					continue;
			}

			if( !isset($this->dto->keihiItiran[$row['id']]) ){
				$this->dto->keihiItiran[ $row['id'] ] =
					[
						'keihiAccountName'        => $row['name'],
						'keihiAmountTotal'        => 0,
						'zKeihiAmountTotal'       => 0,
					];
			}
			if ($this->dto->reportSubType !== '前期比較'){
				$this->dto->keihiItiran[$row['id']]['keihiAmountTotal'] +=
					($row['side'] === 'debit') ? $row['total'] : -$row['total'];
			}else{
				$this->dto->keihiItiran[$row['id']]['zKeihiAmountTotal'] +=
					($row['side'] === 'debit') ? $row['total'] : -$row['total'];
			}
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