<h1>仕分伝票修正-対象伝票検索</h1>
<?php
require_once ROOT_PATH . '/views/lib/ProcSlct.php';
$details = $this->VoucherDto->DtoDetails;
?>
単独検索(伝票No，取引日付，取引金額，摘要欄あいまい検索)は、検索条件を入力して「検索」ボタンを押してください。<br>
<form method="POST" action="index.php?route=voucher.list">
    伝票No <input type="text" name="ListDate" value="<?= h($_POST['ListDate'] ?? '') ?>"><br>
    日付 <input type="date" name="ListDate" value="<?= h($_POST['ListDate'] ?? '') ?>"><br>
    摘要 <input type="text" name="ListSummary" value="<?= h($_POST['ListSummary'] ?? '') ?>"><br>
    <hr>
    <button name="SimpleSearch" type="submit">検索</button>
</form>
<hr>
<?