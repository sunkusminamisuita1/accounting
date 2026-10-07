<?php 
require_once ROOT_PATH . '/app/dto/constants.php';
?>
<!DOCTYPE html>
    <html lang="ja">
            <head>
                <meta charset="UTF-8">
                    <title>試算表テスト</title>
                <style>
                    table {border: none;
                    }

                    th { border: 0px solid #ccc; padding: 8px; text-align: right;
                        background: #f4f4f4; text-align: center; 
                    }

                    th { background: #f4f4f4; text-align: center; 
                    }

                    td { border: 0px solid #ccc; padding: 8px; text-align: right; 
                    }

                    .text-left { text-align: left; 
                    }

                    .fs2 {font-size: 20px; vertical-align: middle;
                    }
                    /* テーブル全体のデザイン */
                    .tb-style {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }
                    /* th行：センター、太字 */
                    .tb-style th {
                        text-align: center;
                        font-weight: bold;
                        padding:8px;
                        border-bottom: 2px solid #ccc; /* 見出しの下に線を引いて見やすくしています */
                    } 
                    /* td共通の余白 */
                    .tb-style td {
                        padding: 6px 8px;
                        border-bottom: 1px solid #eee; /* 行ごとの区切り線 */
                    }
                    /* 科目名：センター */
                    .tb-style .col-name {
                        text-align: center;
                    }
                    /* 金額：右詰め */
                    .tb-style .col-amount {
                        text-align: right;
                        font-family: 'Courier New', Courier, monospace; /* 数字の桁が綺麗に揃うフォント（お好みで） */
                    }
                    .procSlct { border-collapse: collapse; width: auto; 
                    } /* 幅は中身に合わせるのが一般的 */
                    .procSlct button { cursor: pointer; padding: 5px 15px; 
                    }
                </style>
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
    'home'           => 'ホーム',
    'voucher.create' => '仕分処理',
    'voucher.list'   => '仕分伝票修正',
    'accounts.edit'  => '勘定科目追加',
    'shop.edit'      => '店舗情報編集',
    'logout'         => 'ログアウト'
//    $url             => '戻る', //呼び出し元に戻るボタンを追加  
];

$route = $_GET['route'] ?? '';
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
<table>   <!-- ページ分割　-->
<tr>   <!-- ページ分割　-->
<td>   <!-- ページ分割　-->
<table class="procSlct">

    <tr>
        <td>
            コンビニ会計(複数 店舗 事業 対応)
        </td>
        <td colspan="<?= count($display_buttons) + 1; ?>" style="text-align: center; ">

            <div class="shop-selector-container" style="display: inline-block; text-align: left;">
                <label for="active_shop">ようこそ 
                    <?= htmlspecialchars($_SESSION['user']['username'] ?? 'ゲスト') ?>
                　　現在の操作店舗：</label>
                <form action="index.php?route=shop.switch" method="POST" id="shop_selector_form" style="display: inline;">

                    <select name="active_shop" id="active_shop" 
                        onchange="document.getElementById('shop_selector_form').submit();">

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
<br><br>
</td>   <!-- ページ分割　-->
</tr>   <!-- ページ分割　-->
<tr>   <!-- ページ分割　-->
<td>   <!-- ページ分割　-->
<table class="procSlct">
    <tr>
        <td colspan="<?= count($display_buttons) + 1; ?>" style="text-align: center; padding-top: 15px;">
            <p>帳票メニュー</p>
        </td>
    </tr>
</table>

                        <?php
                        $validationItems = [];
                        foreach ($this->dto->errData ?? [] as $field => $msg) {
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
</td>   <!-- ページ分割　-->
<td>   <!-- ページ分割　-->
<?php
            if($viewType === "sisanhyou"){
                require_once ROOT_PATH.'/views/subView/reportMenu.php'; 
            }
?>
</td>   <!-- ページ分割　-->
<td>   <!-- ページ分割　-->
 <?php
            if($viewType === "sisanhyou"){
                require_once ROOT_PATH.'/views/subView/sisanhyou.php'; 
            }

?>
</td>   <!-- ページ分割　-->
</tr>   <!-- ページ分割　-->
</table>
</html>