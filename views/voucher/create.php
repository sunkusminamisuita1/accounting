<h1>仕訳入力</h1>

<form method="POST" action="index.php?route=voucher.store">

    日付
    <input type="date" name="voucher_date"><br>

    摘要
    <input type="text" name="summary"><br>

    <hr>

    <table border="1">
        <tr>
            <th>科目</th>
            <th>金額</th>
            <th>区分</th>
        </tr>

        <tr>
            <td>
                <select name="details[0][account_id]">
                    <?php foreach($accounts as $a): ?>
                        <option value="<?= $a['id'] ?>">
                            <?= h($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <input type="number" name="details[0][amount]">
            </td>

            <td>
                <select name="details[0][side]">
                    <option value="debit">借方</option>
                    <option value="credit">貸方</option>
                </select>
            </td>
        </tr>

        <tr>
            <td>
                <select name="details[1][account_id]">
                    <?php foreach($accounts as $a): ?>
                        <option value="<?= $a['id'] ?>">
                            <?= h($a['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <input type="number" name="details[1][amount]">
            </td>

            <td>
                <select name="details[1][side]">
                    <option value="debit">借方</option>
                    <option value="credit">貸方</option>
                </select>
            </td>
        </tr>

    </table>

    <button type="submit">保存</button>

</form>