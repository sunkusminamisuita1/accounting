<?php
//この部分は仮　将来的に　accountsテーブルに userId追加し,login.phpに追加？
$pdo = getPDO();
$accounts = $pdo->query("SELECT id, name FROM accounts ORDER BY id")->fetchAll();
?>
<h2>伝票入力</h2>
<form method="post" action="?route=voucher.store">
	<input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

	日付:
	<input type="date" name="voucher_date" required><br><br>

	摘要:
	<input type="text" name="summary"><br><br>

	<h3>借方</h3>
		<select name="debit_account_id" required>
<?php foreach ($accounts as $a): ?>
			<option value="<?= $a['id'] ?>">
			<?= htmlspecialchars($a['name']) ?>
			</option>
<?php endforeach; ?>
		</select>
	金額:
	<input type="number" name="debit_amount" required><br><br>

	<h3>貸方</h3>
	<select name="credit_account_id" required>
<?php foreach ($accounts as $a): ?>
		<option value="<?= $a['id'] ?>">
            <?= htmlspecialchars($a['name']) ?>
		</option>
<?php endforeach; ?>
	</select>
	金額:
	<input type="number" name="credit_amount" required><br><br>

	<button type="submit">登録</button>
</form>
