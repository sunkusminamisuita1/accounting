<?php
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
require_once ROOT_PATH . '/lib/helpers.php';
//require_once ROOT_PATH . '/app/repositories/kessanRepository.php';

class kessanRepository{
	private $dto;
	private $pdo;

    public function __construct($dto, $pdo) {
		$this->dto	= $dto;	
		$this->pdo	= $pdo;
	}

	function getGetujiKeihiData(){

		$sql = "SELECT jv.id AS jvId, jv.voucher_date, jv.summary,
		               jd.jd_summary, jd.side, jd.amount,
					   ac.id, ac.sort_order, ac.type, ac.name,
					   SUM(jd.amount) AS total
					FROM   journal_vouchers jv
					JOIN   journal_details  jd     ON jv.id      =   jd.voucher_id
					JOIN   accounts ac             ON ac.id      =   jd.account_id
						
						WHERE    jv.user_id      =  :userId    AND
								 jv.voucher_date >= :fromDate  AND
								 jv.voucher_date <= :toDate ";

		if($this->dto->session['currentShopCode']  !==  '   all'){
			$sql     .= "AND jv.shop_code = " . $this->dto->session['currentShopCode'] . ' ';
		}

		$sql     .= "GROUP BY ac.id, jd.side ";
		//$sql     .= "ORDER BY jv.voucher_date ASC , ac.sort_order ASC "  ;
						
		$userId   = $this->dto->session['user']['id'];
		$fromDate = $this->dto->from;
		$toDate   = $this->dto->to;
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute( [ ':userId' => $userId, ':fromDate' => $fromDate, ':toDate' => $toDate] );
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		//dbg('kessanrepo//getGetujiKeihiData', $rows);
		return $rows;
	}

	function getTrial( $from, $to ){

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
			//$userId = $_SESSION['user']['user_id'];  ############################   $this->dto->activeShop
			$userId = $_SESSION['user']['id'];
			$shopCode = $this->dto->session['currentShopCode'] ?? '   all';

			if($shopCode !== '   all'){
				$sql = $sql0 . $sql2 . $sql3 ;
			}else{
			//shopCode '   all'連結決算の場合
				$sql = $sql0 . $sql1 . $sql3 ;
			}

			$stmt = $this->pdo->prepare($sql);

			if($shopCode !== '   all'){
				$stmt->execute( [ ':from' => $from, ':to' => $to, ':userId' => $userId, ':shopCode' => $shopCode ] );
			}else{
			//shopCode '   all'連結決算の場合
				$stmt->execute( [ ':from' => $from, ':to' => $to, ':userId' => $userId ] );
			}

			
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			//dbg('kessanrepo//getTrial', $rows);
			//echo "<br>入力ショップコード: " . var_dump($shopCode) . "<br>";
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
	function getKeihiItiran( $from, $to){
		// Implementation for fetching expense list
		if(!$from || !$to){
			return [];
		};
		$sql0 = 
			"SELECT	
				a.id		as account_id,
				a.name	as name,
				a.type	as type,
				a.is_deleted as is_deleted,
				jd.side,
				jd.amount AS amount
			FROM journal_details jd
			JOIN journal_vouchers jv	ON jd.voucher_id = jv.id
			JOIN accounts a 			ON jd.account_id = a.id
			";
		$sql1 =	
			"WHERE jv.voucher_date BETWEEN :from AND :to
				AND jv.user_id = :userId
				AND a.type = :type
			";
		$sql2 =	
			"WHERE jv.voucher_date BETWEEN :from AND :to
				AND jv.user_id = :userId
				AND jv.shop_code = :shopCode
				AND a.type = :type
			";
		$sql3 =
			"GROUP BY a.id, a.name, a.type, jd.side
			ORDER BY a.id
			";
			//$userId = $_SESSION['user']['user_id'];  ############################   $this->dto->activeShop
			$userId = $_SESSION['user']['id'];
			$shopCode = $this->dto->session['currentShopCode'] ?? '   all';
			$type = '費用';

			if($shopCode !== '   all'){
				$sql = $sql0 . $sql2  ;
			}else{
			//shopCode '   all'連結決算の場合
				$sql = $sql0 . $sql1  ;
			}

			$stmt = $this->pdo->prepare($sql);

			if($shopCode !== '   all'){
				$stmt->execute( [ ':from' => $from, ':to' => $to, ':userId' => $userId, ':shopCode' => $shopCode, ':type' => $type ] );
			}else{
			//shopCode '   all'連結決算の場合
				$stmt->execute( [ ':from' => $from, ':to' => $to, ':userId' => $userId, ':type' => $type ] );
			}

			
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			//dbg('kessanrepo//getKeihiItiran', $rows);
			//echo "<br>入力ショップコード: " . var_dump($shopCode) . "<br>";
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
			$trial[$id][$row['side']] += $row['amount'];
		}
		return $trial;
	}
}
?>
