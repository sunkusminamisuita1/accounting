<h1>仕訳入力</h1>
<?php
require_once ROOT_PATH . '/views/lib/ProcSlct.php';

$details = $_POST['details'] ?? [
    0 => [],
];
if($_POST['add_row'] ?? false) {
    $details[] = [];
} 
if($_POST['delete_row'] ?? false) {
    $idx = (int)$_POST['delete_row'];
    unset($details[$idx]);
    $details = array_values($details); // インデックスを並べ直す
} 
?>

<?php if (!empty($this->VoucherDto->ErrData)): ?>
    <ul style="color: red;">
        <?php foreach ($this->VoucherDto->ErrData as $mod => $err): ?>
            <li><?= h($mod) . ": " . h($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST" action="index.php?route=voucher.create">   
    日付 <input type="date" name="voucher_date" value="<?= h($_POST['voucher_date'] ?? '') ?>"><br>
    摘要 <input type="text" name="summary" value="<?= h($_POST['summary'] ?? '') ?>"><br>
    <hr>
    <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
    <table border="1">
        <tr>
            <th>科目</th>
            <th>金額</th>
            <th>区分</th>
            <th>操作</th>
        </tr>
        <?php foreach ($details as $i => $row): ?>
        <tr>
            <td>
                <select name="details[<?= $i ?>][account_id]" required >
                    <option value="">選択してください</option>
                    <?php foreach($accounts as $a): ?>
                        <option value="<?= h($a['id']) ?>" <?= (isset($row['account_id']) && $row['account_id'] == $a['id']) ? 'selected' : '' ?>>
                            <?= h($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <input type="number" name="details[<?= $i ?>][amount]" value="<?= h($details[$i]['amount'] ?? '') ?>" required>
            </td>

            <td>
                <select name="details[<?= $i ?>][side]" required>
                    <option value="debit" <?= h($details[$i]['side'] ?? '') == 'debit' ? 'selected' : '' ?>>借方</option>
                    <option value="credit" <?= h($details[$i]['side'] ?? '') == 'credit' ? 'selected' : '' ?>>貸方</option>
                </select>
            </td>
            <td> 
                <button name="add_row" type="submit" value="add_row">行追加</button> 
            </td>
             <td> 
                <button name="delete_row" type="submit" value="<?= $i ?>" formnovalidate>行削除</button> 
            </td>
        </tr>
        <?php endforeach; ?>

    </table>
    <button name="save" type="submit">保存</button>
</form>
<?php