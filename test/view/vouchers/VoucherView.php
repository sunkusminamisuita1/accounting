<?php
// /test/view/vouchers/VoucherView.php
require_once ROOT_PATH . '/test/DTO/VoucherDTO.php';
require_once ROOT_PATH . '/test/Validators/VoucherValidate.php';

// アクションを取得（ルートから判定）
$route = $_GET['route'] ?? 'voucher.create';
if ($route === 'voucher.create') {
    $action = 'create';
} elseif ($route === 'voucher.add') {
    $action = 'add';
} elseif ($route === 'voucher.delete') {
    $action = 'delete';
} else {
    $action = 'create';
}

$accounts = $GLOBALS['accounts'] ?? [];
$errors = $_SESSION['errors'] ?? [];

// フラッシュメッセージの表示と削除
if (isset($_SESSION['flash_message'])) {
    echo '<div style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin-bottom: 20px;">';
    echo h($_SESSION['flash_message']);
    echo '</div>';
    unset($_SESSION['flash_message']);
}

// エラーメッセージの表示
if (!empty($errors)) {
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 20px;">';
    foreach ($errors as $error) {
        echo '<p>' . h($error) . '</p>';
    }
    echo '</div>';
    unset($_SESSION['errors']);
}

if ($action === 'create') {
    // VCreateView の内容
    $voucherDate = date("Y-m-d");
    $voucherRows = $_SESSION['voucherRows'] ?? [];
    $debitAmountTotal = $_SESSION['debitAmountTotal'] ?? 0;
    $creditAmountTotal = $_SESSION['creditAmountTotal'] ?? 0;
    $slipNum = $_SESSION['slipNum'] ?? 0;

    $diff = $debitAmountTotal - $creditAmountTotal;
    $isBalanced = ($diff === 0 && !empty($voucherRows));
    ?>

    <h2>伝票入力</h2>

    <div style="background-color: <?= $isBalanced ? '#d4edda' : '#fff3cd' ?>; padding: 10px; border: 1px solid #ccc; margin-bottom: 20px;">
        <p><strong>借方合計:</strong> <?= number_format($debitAmountTotal) ?></p>
        <p><strong>貸方合計:</strong> <?= number_format($creditAmountTotal) ?></p>
        <p><strong>差額:</strong> <?= number_format($diff) ?></p>
        <p><strong>バランス:</strong> <span style="color: <?= $isBalanced ? 'green' : 'red' ?>;">
            <?= $isBalanced ? '✓ OK' : '✗ 不一致' ?>
        </span></p>
    </div>

    <div style="margin-bottom: 20px;">
        <form method="post" action="index.php?route=voucher.create">
            <input type="hidden" name="csrfToken" value="<?= h(generateCsrfToken()) ?>">
            <button type="submit" name="clear">全明細を削除</button>
        </form>
    </div>

    <?php if (!empty($voucherRows)): ?>
        <h3>入力明細</h3>
        <form method="post" action="index.php?route=voucher.delete">
            <input type="hidden" name="csrfToken" value="<?= h(generateCsrfToken()) ?>">
            <table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th>削除</th>
                        <th>修正</th>
                        <th>日付</th>
                        <th>借方/貸方</th>
                        <th>勘定科目</th>
                        <th>金額</th>
                        <th>摘要</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($voucherRows as $key => $row): ?>
                        <tr>
                            <td><input type="checkbox" name="deleteKeys[]" value="<?= h($key) ?>"></td>
                            <td><input type="radio" name="update_key" value="<?= h($key) ?>"></td>
                            <td><?= h($row['date']) ?></td>
                            <td><?= h($row['side']) ?></td>
                            <td><?= h($row['accountName']) ?></td>
                            <td><?= number_format($row['amount']) ?></td>
                            <td><?= h($row['summary']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <button type="submit" name="alt">修正削除一括実行</button>
        </form>
    <?php else: ?>
        <p style="color: #999;">入力明細がありません</p>
    <?php endif; ?>

    <h3>新規明細入力</h3>
    <form method="post" action="index.php?route=voucher.add">
        <input type="hidden" name="csrfToken" value="<?= h(generateCsrfToken()) ?>">

        <p>
            <label>伝票日付：</label>
            <input type="date" name="voucherDate" value="<?= h($voucherDate) ?>" required>
        </p>

        <p>
            <label>借方/貸方：</label>
            <select name="side" required>
                <option value="">--選択--</option>
                <option value="借方">借方</option>
                <option value="貸方">貸方</option>
            </select>
        </p>

        <p>
            <label>勘定科目：</label>
            <select name="accountId" required>
                <option value="">--選択--</option>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= h($account['id']) ?>">
                        <?= h($account['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label>金額：</label>
            <input type="number" name="amount" required>
        </p>

        <p>
            <label>摘要：</label>
            <input type="text" name="summary" maxlength="255">
        </p>

        <button type="submit" name="add">明細追加</button>
    </form>

<?php } elseif ($action === 'add') {
    // VAddView の内容
    $editData = $_SESSION['edit_data'] ?? null;
    ?>

    <h3>明細追加</h3>
    <form method="post" action="index.php?route=voucher.add">
        <input type="hidden" name="csrfToken" value="<?= h(generateCsrfToken()) ?>">

        <p>
            <label>伝票日付：</label>
            <input type="date" name="voucherDate" 
                   value="<?= h($editData['date'] ?? date('Y-m-d')) ?>" required>
        </p>

        <p>
            <label>借方/貸方：</label>
            <select name="side" required>
                <option value="">--選択--</option>
                <option value="借方" <?= ($editData['side'] ?? '') === '借方' ? 'selected' : '' ?>>借方</option>
                <option value="貸方" <?= ($editData['side'] ?? '') === '貸方' ? 'selected' : '' ?>>貸方</option>
            </select>
        </p>

        <p>
            <label>勘定科目：</label>
            <select name="accountId" required>
                <option value="">--選択--</option>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= h($account['id']) ?>" 
                        <?= ($editData['accountId'] ?? '') == $account['id'] ? 'selected' : '' ?>>
                        <?= h($account['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label>金額：</label>
            <input type="number" name="amount" 
                   value="<?= h($editData['amount'] ?? '') ?>" required>
        </p>

        <p>
            <label>摘要：</label>
            <input type="text" name="summary" 
                   value="<?= h($editData['summary'] ?? '') ?>" maxlength="255">
        </p>

        <button type="submit" name="add">明細追加</button>
    </form>

    <?php unset($_SESSION['edit_data']); ?>

<?php } elseif ($action === 'delete') {
    // VDeleteView の内容
    ?>

    <h3>削除・修正確認</h3>

    <?php if (isset($_SESSION['edit_data'])): ?>
        <div style="background-color: #e7f3ff; padding: 10px; border: 1px solid #b3d9ff; margin-bottom: 20px;">
            <h4>修正中のデータ</h4>
            <p><strong>日付:</strong> <?= h($_SESSION['edit_data']['date']) ?></p>
            <p><strong>借方/貸方:</strong> <?= h($_SESSION['edit_data']['side']) ?></p>
            <p><strong>勘定科目:</strong> <?= h($_SESSION['edit_data']['accountName']) ?></p>
            <p><strong>金額:</strong> <?= number_format($_SESSION['edit_data']['amount']) ?></p>
            <p><strong>摘要:</strong> <?= h($_SESSION['edit_data']['summary']) ?></p>
        </div>
        <p style="color: #0066cc;">上記のデータを修正して再度追加してください</p>
    <?php endif; ?>

    <p><a href="index.php?route=voucher.create">← 伝票入力に戻る</a></p>

<?php } ?>

<?php unset($_SESSION['errors']); ?>