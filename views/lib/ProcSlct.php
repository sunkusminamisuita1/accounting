<?php
// 1. ルート設定をデータとして定義（保守が楽）

$RtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
$RtnRoute = ltrim(strchr($RtnRoute,'route='), 'route='); //'='前を削除

$all_routes = [
    'home'           => 'ホーム',
    'voucher.create' => '仕分処理',
    'voucher.list'   => '仕分伝票修正',
    'accounts.edit'  => '勘定科目追加',
    'voucher.delete' => '仕分削除',
    'voucher.index'  => '仕分一覧',
    'logout'         => 'ログアウト',
//    $url             => '戻る', //呼び出し元に戻るボタンを追加  
];

$route = $_GET['route'] ?? '';
//echo "route:{$route}<br>";
// 2. ガード節（エラーなら先に終わらせる）
if (empty($route) || ($route !== 'home' && !isset($all_routes[$route]))) {
    DispErrorMsg("ルート「{$route}」は正しくありません。");
    echo "内部エラー : ルートが正しく設定されていません。";
    exit;
}

// 3. 表示ロジック（現在地以外のボタンをONにする）
// 'home' の時は全部ON、それ以外の時は自分以外をONにする
$display_buttons = [];
foreach ($all_routes as $key => $label) {
    //if ($route === 'home' || $route !== $key) {
    if ($route !== $key) {
        $display_buttons[$key] = $label;
    }
}
?>

<style>
/*    .ProcSlct, .ProcSlct td { border: none !important; } */
    .ProcSlct { border-collapse: collapse; width: auto; } /* 幅は中身に合わせるのが一般的 */
    .ProcSlct button { cursor: pointer; padding: 5px 15px; }
</style>

<table class="ProcSlct">
    <tr>
        <?php foreach  ($display_buttons as $key => $label): ?>
            <td>
                <a href="http://test5.local/index.php?route=<?= h($key) ?>">
                    <button type="button"><?= h($label) ?>
                    </button>
                </a>
            </td>
        <?php endforeach; ?>
            <td>
                <a href="http://test5.local/index.php?route=<?= h($RtnRoute) ?>">
                    <button type="button"><?= h('戻る') ?>
                    </button>
                </a>

            </td>




            <td>
                <div class="shop-selector-container">
                    <label for="active_shop">現在の操作店舗：</label>
                    <select name="active_shop" id="active_shop" onchange="location.href='index.php?route=shop.switch&shop_id=' + this.value;">
                        <?php foreach ($_SESSION['user_shops'] as $shop): ?>
                            <option value="<?php echo $shop['id']; ?>" <?php echo ($shop['id'] == $_SESSION['current_shop_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($shop['shop_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                            <option value="all" <?php echo ($_SESSION['current_shop_id'] === 'all') ? 'selected' : ''; ?>>【全店合算（連結決算）】</option>
                    </select>
                </div>

            </td>




    </tr>
</table>
