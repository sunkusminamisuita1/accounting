<h1>仕分伝票修正-対象伝票検索</h1>
<?php
require_once ROOT_PATH . '/views/lib/ProcSlct.php';
$details = $this->VoucherDto->DtoDetails;
?>
<?php if (!empty($this->VoucherDto->ErrData)): ?>
    <ul style="color: red;">
        <?php foreach ($this->VoucherDto->ErrData as $mod => $err): ?>
            <li><?= h($mod) . ": " . h($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
    <br><hr>
<h3>単独検索(伝票No，取引日付，取引金額，摘要欄あいまい検索)は、<br>
    １つ以上の検索条件を入力して検索ボタンを押してください。
</h3>
<form method="POST" action="index.php?route=voucher.list">
    <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
    <table border="1">
        <tr>
            <td>
                伝票No
            </td>

            <td>
                <input type="text" name="ListVcrNum" value="<?= h($_POST['ListVcrNum'] ?? '') ?>">
            </td>

        <tr>　
        </tr>

        <tr>
            <td>
                日付        
            </td>

            <td>
                <input type="date" name="ListVcrDate" value="<?= h($_POST['ListVcrDate'] ?? '') ?>">
            </td>
        </tr>

        <tr>　
        </tr>

        <tr>
            <td>
                摘要
            </td>

            <td>
                <input type="text" name="ListVcrSummary" value="<?= h($_POST['ListVcrSummary'] ?? '') ?>">
            </td>
        </tr>
    </table>
    <br>
    <button name="SimpleSearch" type="submit">検索</button>
</form>
<hr>
<?