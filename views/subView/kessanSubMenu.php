<form action="index.php?route=kessan.report" method="post">決算メニュー<br><br>

    <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">

    <input type="radio" name="reportType"
        value="<?= '決算書' ?>" <?= $currentReport === "決算書" ? 'checked' : '' ?>>
            決算書出力<br><br>

    <input type="radio" name="reportType"
        value="<?= '貸借対照表' ?>" <?= $currentReport === "貸借対照表" ? 'checked' : '' ?>>
            貸借対照表出力<br><br>

    <input type="radio" name="reportType"
        value="<?= '経費一覧' ?>" <?= $currentReport === "経費一覧" ? 'checked' : '' ?>>
            経費/一般管理費出力<br><br>

    <input type="radio" name="reportType"
        value="<?= '勘定元帳' ?>" <?= $currentReport === "勘定元帳" ? 'checked' : '' ?>>
            勘定元帳出力<br><br>

    <input type="radio" name="reportType"
        value="<?= '消費税計算書' ?>" <?= $currentReport === "消費税計算書" ? 'checked' : '' ?>>
            消費税計算書出力<br><br>

    <input type="radio" name="reportType"
        value="<?= '減価償却表'  ?>" <?= $currentReport === "減価償却表" ? 'checked' : '' ?>>
            減価償却表出力<br><br>

    <button type="submit">切替</button><br><br>
    <!-- <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>"> -->
</form>
<!-- ここから下は、sisanhyou.phpに移動-->

