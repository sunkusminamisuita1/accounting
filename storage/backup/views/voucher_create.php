<?php
if (!defined('ROOT_PATH')) {
	exit('Direct access not allowed!');
}
$route = 'voucher.create';
$diff = $_SESSION['debitAmountTotal'] - $_SESSION['creditAmountTotal'];
$is_balanced = ($diff === 0 && !empty($_SESSION['voucherRows']));
if(!isset($_SESSION['debitAmountTotal'])){$_SESSION['debitAmountTotal']=0;}
if(!isset($_SESSION['creditAmountTotal'])){$_SESSION['creditAmountTotal']=0;}
//この部分は仮　将来的に　accountsテーブルに userId追加し,login.phpに追加？
$pdo = getPDO();
//var_dump($_SESSION);
$accounts = $pdo->query("SELECT id, name FROM accounts ORDER BY id")->fetchAll();
$is_first_row = "";
$voucherDate = date("Y-m-d");
array_unshift($accounts,['id'=>9999,'name'=>'----------']);
if(!isset($_SESSION['slipNum'])){
	$_SESSION['slipNum'] = 0;
}
$slipNum = $_SESSION['slipNum'];
$is_first_row = (	empty($_SESSION['voucherRows']));
if (!isset($_SESSION['voucherRows'])){ $_SESSION['voucherRows'] = [];}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verifyCsrfToken($_POST['csrfToken'] ?? '');
	if (isset($_POST['clear'])) {
		unset($_SESSION['voucherRows']);
		$_SESSION['slipNum'] = 0;
		$_SESSION['flash_message'] = "入力内容をすべて削除しました。";
		$_SESSION['creditAmountTotal']=(int)0;
		$_SESSION['debitAmountTotal']=(int)0;
		header('Location:' . "../index.php?route=$route");
		exit;
	}
	if (isset($_POST['alt'])) {
// 削除処理
		if (isset($_POST['deleteKeys'])) {
			foreach ($_POST['deleteKeys'] as $key) {
				unset($_SESSION['voucherRows'][$key]);
			}
		}
// 修正処理（選択した1件を入力欄に戻す）
		if (isset($_POST['update_key'])) {
			$key = $_POST['update_key'];
			$target = $_SESSION['voucherRows'][$key];
// 入力欄に値を戻すためのフラグや値をセット（セッションなど）
			$_SESSION['edit_data'] = $target; 
// 元のデータは一旦消す
			unset($_SESSION['voucherRows'][$key]);
		}
	}
	if (isset($_POST['add'])) {
		$voucherDate	= $_POST['voucherDate']??date("Y-m-d");
		$side			= $_POST['side']??"";
		$accountId		= $_POST['accountId'] ?? null;
		$accountName	= isset($accounts[$accountId]) ?$accounts[$accountId]['name']:"";
		$amount		= $_POST['amount']??"";
		$summary		= $_POST['summary']??"";
		if((int)$accountId !== 9999 ){
			$_SESSION['voucherRows'][$slipNum]	=
					[
					'date'=>$voucherDate,'side'=>$side,'accountId'=>$accountId,
					'accountName'=>$accountName,'amount'=>$amount,'summary'=>$summary
					];
			$_SESSION['slipNum']++;
		}
	}
	$_SESSION['creditAmountTotal']=(int)0;
	$_SESSION['debitAmountTotal']=(int)0;
	foreach($_SESSION['voucherRows'] as $row){
		if($row['side'] === '貸方'){
			$_SESSION['creditAmountTotal'] += (int)$row['amount'];
		}else{
			$_SESSION['debitAmountTotal'] += (int)$row['amount'];
		}
	}
	header('Location: ' . $_SERVER['PHP_SELF'] . "?route=$route");
	exit;
}
	foreach($_SESSION['voucherRows'] as $key=>$row){
		echo "{$key}<br>";
		print_r($row);
		echo "<br>";
	};
	echo "<br>---------<br>";
?>
<?php if (isset($_SESSION['flash_message'])): ?>
	<div style="background-color: #d4edda; color: #155724;
		padding: 10px; border: 1px solid #c3e6cb; margin-bottom: 20px;">
		<?= h($_SESSION['flash_message']) ?>
	</div>
<!-- ★ 一度表示したら、次のリロードでは出ないように消す -->
<?php	unset($_SESSION['flash_message']); ?>
<?php endif; ?>
<h2>伝票入力</h2>
<form method="post" onsubmit="return validateForm()">
	<button type="submit" name="clear">全明細を削除</button>
	<button type="submit" name="add">明細追加</button>
	<button type="submit" name="alt">修正削除一括実行</button>
	
	
	<div style="margin-top: 20px; text-align: right;">
<?php if ($is_balanced): ?>
	<button type="submit" name="save_db" style="padding: 10px 20px; background-color: #28a745; color: white;">
	この伝票を登録する
	</button>
<?php else: ?>
	<p style="color: red;">※貸借合計不一致:登録不可</p>
<?php endif; ?>
	</div>
	
	
	
	<input type="hidden" name="csrfTokenKey" value="<?= h(generateCsrfToken()) ?>">
	<table>
		<tbody>
			<tr>
				<th><h3>伝票日付</h3></th>
				<th><h3>借方/貸方</h3></th>
				<th><h3>科目</h3></th>
				<th><h3>金額</h3></th>
				<th><h3>適用</h3></th>
			</tr>
			<tr>
				<td>
					<input type="date" name="voucherDate" 
						value="<?= h($_SESSION['edit_data']['date'] ?? $voucherDate) ?>"
	 			 		<?= !$is_first_row ? 'readonly' : 'required' ?>>
				</td>
				<td>
					<select name="side">

<?php 
	$wk = $_SESSION['edit_data']['side']==='借方'?'借方':'貸方';
//				echo "target={$_SESSION['edit_data']['side']}";exit;
?>


						<option value="貸方"<?= $wk==='貸方'?'selected':'' ?>>貸方</option>
						<option value="借方"<?= $wk==='借方'?'selected':'' ?>>借方</option>
					</select>
				</td>
				<td>
					<select name="accountId">
<?php 
foreach ($accounts as $a): 
// 選択状態を事前に判定しておく
	$selected = (isset($_SESSION['edit_data']['accountId']) &&
			$_SESSION['edit_data']['accountId'] == $a['id']) ? 'selected' : '';
?>
						<option value="<?= h($a['id']) ?>" <?= $selected ?>>
							<?= h($a['name']) ?>
						</option>
<?php endforeach; ?>
					</select>					
				</td>
				<td>
					<input type="number" name="amount" 
						value="<?= h($_SESSION['edit_data']['amount'] ?? '') ?>">
				</td>	
				<td>
					<input type="text" name="summary"
						value="<?= h($_SESSION['edit_data']['summary'] ?? '') ?>">
				</td>
			</tr>
		
		</tbody>
	</table>

	<table>
		<tbody>
			<tr>
				<th>日付</th><th>借方科目</th><th>借方金額</th><th>貸方科目</th><th>貸方金額</th>
				<th>適用</th><th>削除</th><th>修正</th>
			</tr>
<?php foreach ($_SESSION['voucherRows'] as $key=>$row): ?>
			<tr>
				<td><?= $row['date']; ?></td>
				<td><?= $row['side'] ==='借方'?h($row['accountName']):''; ?></td>
				<td class="amount"><?= $row['side'] ==='借方'?
					h(number_format((float)($row['amount']?:0))):''; ?></td>
				<td><?= $row['side'] ==='貸方'?h($row['accountName']):''; ?></td>
				<td class="amount"><?= $row['side'] ==='貸方'?
					h(number_format((float)($row['amount']?:0))):''; ?></td>
				<td><?= h($row['summary']); ?></td>
				<td><input type="checkbox" name="deleteKeys[]" value="<?= h($key) ?>"></td>
				<td><input type="radio" name="update_key" value="<?= h($key) ?>"></td>
			</tr>
<?php endforeach; ?>
<!--
			<tr>
				<td></td><td></td>
				<td class="amount">
					<?= "借方合計" . h(number_format($_SESSION['debitAmountTotal'])); ?></td>
				<td></td>
				<td class="amount">
					<?= "貸方合計" . h(number_format($_SESSION['creditAmountTotal'])); ?></td>
				<td></td><td></td><td></td>
			</tr>
-->
<?php
?>
			<tr style="background-color: <?= $is_balanced ? '#e3f2fd' : '#fff5f5' ?>;">
				<td colspan="2">合計</td>
				<td class="amount"><?= h(number_format($_SESSION['debitAmountTotal'])) ?></td>
				<td></td>
				<td class="amount"><?= h(number_format($_SESSION['creditAmountTotal'])) ?></td>
				<td colspan="3">
<?php if ($diff !== 0): ?>
					<b style="color: red;">差額: <?= h(number_format(abs($diff))) ?></b>
<?php else: ?>
					<b style="color: green;">貸借一致 ✓</b>
<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<style>
/* 表全体のスタイル */
table {
    width: 100%;
    border-collapse: collapse; /* 境界線を1本にまとめる */
    margin: 20px 0;
    font-size: 14px;
}

/* 共通の罫線とパディング */
table th, table td {
    border: 1px solid #ccc; /* 薄いグレーの罫線 */
    padding: 8px 12px;
    text-align: center; /* 基本は中央揃え */
}

/* ヘッダー（th）の網掛け */
table th {
    background-color: #f2f2f2; /* 薄いグレーの網掛け */
    color: #333;
    font-weight: bold;
}

/* 数字（金額）カラムの右揃え */
/* クラス名 .amount を持つセルに適用 */
.amount {
    text-align: right;
}

/* 行にマウスを乗せた時に色を変える（視認性向上） */
table tr:hover {
    background-color: #fafafa;
}
</style>
<script>
function validateForm() {
	// もし「全明細を削除」が押されていたら、チェックせずに送信を許可
	if (document.activeElement && document.activeElement.name === 'clear') {
		return true;
	}
	// もし「行削除」が押されていたら、チェックせずに送信を許可
	if (document.activeElement && document.activeElement.name === 'alt') {
		return true;
	}
	// フォームの各値を取得
	const id = document.querySelector('[name="accountId"]').value;
	const amount = Number(document.querySelector('[name="amount"]').value);

	// 9999は文字列として比較します
	const isValid = (id !== "9999" && amount > 0);

	if (isValid) {
		return true; // 送信を許可
	} else {
		alert("有効な科目と0より大きい金額を入力してください。");
		return false; // 送信を中止
	}
}
</script>

