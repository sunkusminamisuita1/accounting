<h1>伝票編集</h1>

<form method="POST" action="index.php?route=voucher.update">

<input type="hidden" name="id" value="<?= $voucher['id'] ?>">

日付
<input type="date" name="date"
value="<?= h($voucher['voucher_date']) ?>">

摘要
<input type="text" name="summary"
value="<?= h($voucher['summary']) ?>">

<button type="submit">更新</button>

</form>