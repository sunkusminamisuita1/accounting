<h1>仕訳入力</h1>
<?php
require_once ROOT_PATH . '/views/lib/ProcSlct.php';

// 1. まずPOSTされたデータを取得
$details = $_POST['details'] ?? [
    ['account_id' => '', 'amount' => '', 'side' => 'debit'] // 初期1行目
];

// 2. 行追加ボタンが押された場合のみ、末尾に空の要素を追加
if (isset($_POST['add_row'])) {
    $details[] = ['account_id' => '', 'amount' => '', 'side' => 'debit'];
}

// デバッグ用確認
// print_r($details); 
?>

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
                <select name="details[<?= $i ?>][account_id]" required>
                    <option value="">選択してください</option>
                    <?php foreach($accounts as $a): ?>
                        <option value="<?= h($a['id']) ?>" <?= (isset($row['account_id']) && $row['account_id'] == $a['id']) ? 'selected' : '' ?>>
                            <?= h($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <input type="number" name="details[<?= $i ?>][amount]" value="<?= h($row['amount'] ?? '') ?>" required>
            </td>

            <td>
                <select name="details[<?= $i ?>][side]" required>
                    <option value="debit" <?= ($row['side'] ?? '') == 'debit' ? 'selected' : '' ?>>借方</option>
                    <option value="credit" <?= ($row['side'] ?? '') == 'credit' ? 'selected' : '' ?>>貸方</option>
                </select>
            </td>
            <td> 
                <button name="add_row" type="submit" value="1">行追加</button> 
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <button type="submit" name="save">保存</button>
</form>