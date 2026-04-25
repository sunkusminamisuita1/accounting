<?php
// 1. ルート設定をデータとして定義（保守が楽）
$all_routes = [
    'voucher.create' => '仕分処理',
    'voucher.edit'   => '仕分修正',
    'voucher.delete' => '仕分削除',
    'voucher.index'  => '仕分一覧',
    'logout'         => 'ログアウト',
];

$route = $_GET['route'] ?? '';

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
    if ($route === 'home' || $route !== $key) {
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
                <a href="index.php?route=<?= h($key) ?>">
                    <button type="button"><?= h($label) ?></button>
                </a>
            </td>
        <?php endforeach; ?>
    </tr>
</table>
