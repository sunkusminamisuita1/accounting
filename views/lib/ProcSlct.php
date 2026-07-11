<?php
// 1. ルート設定をデータとして定義（保守が楽）

$RtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
$RtnRoute = ltrim(strchr($RtnRoute,'route='), 'route='); //'='
//使用方法　http://test5.local/index.php?route=<?= h($RtnRoute) 

$all_routes = [
    'home'           => 'ホーム',
    'voucher.create' => '仕分処理',
    'voucher.list'   => '仕分伝票修正',
    'accounts.edit'  => '勘定科目追加',
    'voucher.delete' => '仕分削除',
    'voucher.index'  => '仕分一覧',
    'logout'         => 'ログアウト',
    'shop.edit'      => '店舗情報編集'
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

<?php
// 1. プロトコル（http:// または https://）の判定
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

// 2. ドメイン名（例: localhost や example.com）の取得
$host = $_SERVER['HTTP_HOST'];

// 3. パスとクエリ文字列（例: /index.php?id=5）の取得
$requestUri = $_SERVER['REQUEST_URI'];

$requestRoute = $_GET['route'] ?? 'home'; 

$_SESSION['current_route'] = $requestRoute;

?>

<table class="ProcSlct">

    <tr>



        <td colspan="<?= count($display_buttons) + 1; ?>" style="text-align: center; padding-bottom: 15px;">

            <div class="shop-selector-container" style="display: inline-block; text-align: left;">
                <label for="active_shop">現在の操作店舗：</label>
                <!-- フォームを配置し、methodをpostにする -->
                <form action="index.php?route=shop.switch" method="POST" id="shop_selector_form" style="display: inline;">
                    <select name="active_shop" id="active_shop" onchange="document.getElementById('shop_selector_form').submit();">
                        <?php foreach ($_SESSION['user_shops'] as $shop): ?>
                            <option value="<?php echo $shop['shop_code']; ?>" <?php echo ($shop['shop_code'] == $_SESSION['current_shop_code']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($shop['shop_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="all" <?php echo ($_SESSION['current_shop_code'] === 'all') ? 'selected' : ''; ?>>【全店合算（連結決算）】</option>
                    </select>
                </form>
            </div>

        </td>


<?php  /*
        <td colspan="<?= count($display_buttons) + 1; ?>" style="text-align: center; padding-bottom: 15px;">

                <div class="shop-selector-container">
                    <label for="active_shop">現在の操作店舗：</label>
                    <!-- フォームを配置し、methodをpostにする -->
                    <form action="index.php?route=shop.switch" method="POST" id="shop_selector_form">
                        <select name="active_shop" id="active_shop" onchange="document.getElementById('shop_selector_form').submit();">
                            <?php foreach ($_SESSION['user_shops'] as $shop): ?>
                                <option value="<?php echo $shop['id']; ?>" <?php echo ($shop['id'] == $_SESSION['current_shop_code']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($shop['shop_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="all" <?php echo ($_SESSION['current_shop_code'] === 'all') ? 'selected' : ''; ?>>【全店合算（連結決算）】</option>
                        </select>
                    </form>
                </div>

        </td>
*/ ?>


    </tr>

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
    </tr>
</table>
