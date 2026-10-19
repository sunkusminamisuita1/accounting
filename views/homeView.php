<?php 
require_once ROOT_PATH . '/app/dto/constants.php';
?>
<!DOCTYPE html>
    <html lang="ja">
            <head>
                <meta charset="UTF-8">
                    <title>試算表テスト</title>
                    <link rel="stylesheet" href="/css/home.css">
            </head>
                <body>
                    <?php if (!empty($_SESSION['flash_message'])): ?>
                        <script>
                            alert(<?= json_encode($_SESSION['flash_message']) ?>);
                        </script>
                    <?php unset($_SESSION['flash_message']); endif; ?>

<?php
$RtnRoute = $_SERVER['HTTP_REFERER']??'route=home'; //呼び出し元URLを取得
$RtnRoute = ltrim(strchr($RtnRoute,'route='), 'route='); //'='
//使用方法　http://test5.local/index.php?route=<?= h($RtnRoute) 

$all_routes = [
    'home'                  => 'ホーム',
    'voucher.create'        => '仕分処理',
    'voucher.list'          => '仕分伝票修正',
    'accounts.edit'         => '勘定科目追加',
    'shop.edit'             => '店舗情報編集',
    'home.report'           => '帳票メニュー',
    $RtnRoute . ' '         => '戻る', //呼び出し元に戻るボタンを追加
    'login'                 => 'ログイン',
    'logout'                => 'ログアウト'
];

$route = $_GET['route'] ?? 'logout'; // デフォルトのルートを設定
$route = $route ?? 'logout'; // デフォルトのルートを設定
// 2. ガード節（エラーなら先に終わらせる）
if (empty($route) || ($route !== 'home' && !isset($all_routes[$route]))) {
    dispErrorMsg("ルート「{$route}」は正しくありません。");
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

// 1. プロトコル（http:// または https://）の判定
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

// 2. ドメイン名（例: localhost や example.com）の取得
$host = $_SERVER['HTTP_HOST'];

// 3. パスとクエリ文字列（例: /index.php?id=5）の取得
$requestUri = $_SERVER['REQUEST_URI'];

$requestRoute = $_GET['route'] ?? 'home'; 

$_SESSION['current_route'] = $requestRoute;

?>

<header class="home-header">
    <h1 class="home-title">コンビニ会計 <span>複数店舗・事業対応</span></h1>
    <div class="shop-selector-container">
        <p class="shop-greeting">ようこそ <?= htmlspecialchars($_SESSION['user']['username'] ?? 'ゲスト') ?></p>
        <form action="index.php?route=shop.switch" method="POST" id="shop_selector_form" class="shop-selector-form">
            <label for="active_shop">現在の操作店舗</label>
            <select name="active_shop" id="active_shop"
                onchange="document.getElementById('shop_selector_form').submit();">
                <?php foreach ($_SESSION['userShops'] as $shop): ?>
                    <option value="<?php echo $shop['shop_code']; ?>"
                        <?php echo ($shop['shop_code'] == $_SESSION['currentShopCode']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($shop['shop_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
                <option value="   all" <?php echo (($_SESSION['currentShopCode']??'') === '   all') ? 'selected' : ''; ?>>
                    【全店合算（連結決算）】
                </option>
            </select>
        </form>
    </div>
</header>

<nav class="home-nav" aria-label="メインメニュー">
    <?php foreach ($display_buttons as $key => $label): ?>
        <a href="http://test5.local/index.php?route=<?= h($key) ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
</nav>

<!--
        <td colspan="<?= count($display_buttons) + 1; ?>" style="text-align: center; padding-top: 15px;">
-->
                        <?php
                        $validationItems = [];
                        foreach (($this->dto->errData ?? []) as $field => $msg) {
                            // OwnUrlキーは http:// or https:// で始まるので除外
                            if (is_string($field) && (strpos($field, 'http://') === 0 || strpos($field, 'https://') === 0)) {
                                continue;
                            }
                            // 重複メッセージは除去（fieldごとに表示）
                            if (!in_array($msg, $validationItems, true)) {
                                $validationItems[$field] = $msg;
                            }
                        }
                        ?>
                        <div id="validation-messages" style="min-height:3.6em; margin-bottom:1em;">
                            <?php if (!empty($validationItems)): ?>
                                <ul id="validation-list" style="color: red; margin:0; padding-left:1.2em;">
                                    <?php foreach ($validationItems as $field => $m): ?>
                                        <li data-field="<?= h($field) ?>" style="cursor:pointer; text-decoration:underline;"><?= h($m) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <!-- プレースホルダ: 空でも高さを確保 -->
                            <?php endif; ?>
                        </div>
<!-- <?php echo "method: " . ($method ?? 'null'); ?> -->
<?php if (($viewType??'') === "sisanhyou" && ($method??'') === 'report'): ?>
    <div class="report-layout">              <!-- 試算表メニュー -->
        <div class="report-menu">
            <?php require_once ROOT_PATH . '/views/subView/reportSubMenu.php'; ?>
        </div>
        <div class="report-content">         <!-- 試算表内容 -->
            <?php require_once ROOT_PATH . '/views/subView/sisanhyou.php'; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (($controllerName??'') === "voucherController" && ($method??'') === 'create'): ?>
    <div class="report-layout">              <!-- 仕分け伝票作成 -->
        <div class="report-content">         <!-- 仕分け伝票作成内容 -->
            <?php require_once ROOT_PATH . '/views/subView/voucherCreate.php'; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (($controllerName??'') === "voucherController" && ($method??'') === 'list'): ?>
    <div class="report-layout">              <!-- 仕分け伝票修正 -->
        <div class="report-content">         <!-- 仕分け伝票修正内容 -->
            <?php require_once ROOT_PATH . '/views/subView/list.php'; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (($controllerName??'') === "authController" && ($method??'') === 'login'): ?>
    <div class="report-layout">              <!-- ログイン -->
        <div class="report-content">
            <?php require_once ROOT_PATH . '/views/login.php'; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (($controllerName??'') === "shopController" && ($method??'') === 'edit'): ?>
    <div class="report-layout">              <!-- 店舗情報 作成、修正、削除 -->
        <div class="report-content">
            <?php require_once ROOT_PATH . '/views/subView/shopsView.php'; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (($controllerName??'') === "accountsController" && ($method??'') === 'index'): ?>
    <div class="report-layout">              <!-- 店舗情報 作成、修正、削除 -->
        <div class="report-content">
            <?php require_once ROOT_PATH . '/views/subView/accountsView.php'; ?>
        </div>
    </div>
<?php endif; ?>








<!--require ROOT_PATH . '/views/homeView.php';-->

</table>
</html>