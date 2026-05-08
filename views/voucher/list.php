<h1>仕分伝票修正-対象伝票検索</h1>
<?php
require_once ROOT_PATH . '/views/lib/ProcSlct.php';
$details = $this->VoucherDto->DtoDetails;
?>
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
                <input type="text" name="ListDate" value="<?= h($_POST['ListDate'] ?? '') ?>">
            </td>

        <tr>　
        </tr>

        <tr>
            <td>
                日付        
            </td>

            <td>
                <input type="date" name="ListDate" value="<?= h($_POST['ListDate'] ?? '') ?>">
            </td>
        </tr>

        <tr>　
        </tr>

        <tr>
            <td>
                摘要
            </td>

            <td>
                <input type="text" name="ListSummary" value="<?= h($_POST['ListSummary'] ?? '') ?>">
            </td>
        </tr>
    </table>
    <br>
    <button name="SimpleSearch" type="submit">検索</button>
</form>
<hr>
<?