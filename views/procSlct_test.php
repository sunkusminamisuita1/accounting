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
    'shop.edit'      => '店舗情報編集',
    'logout'         => 'ログアウト'
//    $url             => '戻る', //呼び出し元に戻るボタンを追加  
];

$route = $_GET['route'] ?? '';
//echo "route:{$route}<br>";
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
?>

<style>
/*    .procSlct, .procSlct td { border: none !important; } */
    .procSlct { border-collapse: collapse; width: auto; } /* 幅は中身に合わせるのが一般的 */
    .procSlct button { cursor: pointer; padding: 5px 15px; }

    .container {
    text-align: left; /* または text-align: start; */
    }

        /* 1. テーブルセルを左寄せにし、必要ならセルの左側に余白（マージン/パディング）をつける */
    .custom-td {
        text-align: left;
        /* padding-left: 20px; ← セル自体の内側左余白を変えたい場合はここに記述 */
    }

    /* 2. ボタンの左マージン（余白）を設定する */
    .custom-btn {
        margin-left: 15px; /* ここでお好みのマージンに変更してください（例: 10px, 2em など） */
    }
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

<table class="procSlct">

    <tr>
        <td colspan="<?= count($display_buttons) + 1; ?>" style="text-align: center; padding-bottom: 15px;">

            <div class="shop-selector-container" style="display: inline-block; text-align: left;">
                <!-- <p>ようこそ <?= htmlspecialchars($_SESSION['user']['userName'] ?? 'ゲスト') ?></p> -->
                <label for="active_shop">ようこそ <br><?= htmlspecialchars($_SESSION['user']['username'] ?? 'ゲスト') ?>
                    <br><br>
                　　現在の操作店舗：</label>
                    <br>
                <!-- フォームを配置し、methodをpostにする -->
                <form action="index.php?route=shop.switch" method="POST" id="shop_selector_form" style="display: inline;">

                    <select name="active_shop" id="active_shop" onchange="document.getElementById('shop_selector_form').submit();">

                        <?php foreach ($_SESSION['userShops'] as $shop): ?>

                            <option value="<?php echo $shop['shop_code']; ?>" 
                                <?php echo ($shop['shop_code'] == $_SESSION['currentShopCode']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($shop['shop_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>

                        <?php endforeach; ?>

                        <option value="   all" <?php echo ($_SESSION['currentShopCode'] === '   all') ? 'selected' : ''; ?>>
                            【全店合算（連結決算）】
                        </option>
                    </select>
                </form>
            </div>

        </td>

    </tr>
    <tr>
        <td  class="custom-td">
            <br>メインメニュー<br>
        </td>
    </tr>

    <tr>
        <?php foreach  ($display_buttons as $key => $label): ?>

            <tr>
                <td class="custom-td">
                    <a href="http://test5.local/index.php?route=<?= h($key) ?>">
                        <button  type="button" class="custom-btn" >
                            <?= h($label) ?>
                        </button>
                    </a>
                </td>
            </tr>

        <?php endforeach; ?>
            <tr>
                <td  class="custom-td">
                    <br>帳票メニュー<br>
                </td>
            </tr>
                <td class="custom-td">
                    <a href="http://test5.local/index.php?route=<?= h($RtnRoute) ?>">
                        <button type="button" class="custom-btn">
                            <?= h('戻る') ?>
                        </button>
                    </a>
                </td>
    </tr>
</table>
    <br><br>
<table class="procSlct">
    <tr>
        <td colspan="<?= count($display_buttons) + 1; ?>" style="text-align: center; padding-top: 15px;">
            <p>帳票メニュー</p>
        </td>
    </tr>
</table>
