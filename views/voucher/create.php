<?php $token = generateCsrfToken(); ?>
<h1>伝票入力</h1>
    <form method="post" action="index.php?route=voucher.add">
        <input type="hidden" name="csrfTokenKey" value="<?=h($_SESSION['csrfTokenKey'])?>">
        <input type="hidden" name="csrfTokenTime" value="<?=h($_SESSION['csrfTokens'][$_SESSION['csrfTokenKey']])?>">
            <select name="account_id">
<?php foreach($accounts as $a): ?>
                <option value="<?=$a['id']?>">
<?=h($a['name'])?>
                </option>
<?php endforeach; ?>
            </select>
        <input type="number" name="amount">
            <select name="side">
                <option value="debit">借方</option>
                <option value="credit">貸方</option>
            </select>
        <button>追加</button>
    </form>
    <hr>
<h3>借方</h3>
<?php foreach($debits as $d): ?>
    <?=$d['account_id']?>
    <?=$d['amount']?><br>
<?php endforeach; ?>
<h3>貸方</h3>
<?php foreach($credits as $c): ?>
    <?=$c['account_id']?>
    <?=$c['amount']?><br>
<?php endforeach; ?>
    <hr>
<form method="post" action="index.php?route=voucher.store">
    <input type="hidden" name="csrfToken" value="<?=h($token)?>">
        日付
    <input type="date" name="voucher_date">
        摘要
    <input type="text" name="summary">
    <button>伝票保存</button>
</form>
