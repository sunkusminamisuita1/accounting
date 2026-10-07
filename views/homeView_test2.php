<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>試算表テスト</title>

    <style>
        /* =========================
           基本設定
           ========================= */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 20px;
            font-family: sans-serif;
        }


        /* =========================
           ページ上部
           ========================= */

        .page-header {
            width: 100%;
        }

        .page-title {
            font-size: 20px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .shop-area {
            text-align: center;
            vertical-align: middle;
        }

        .shop-area select {
            padding: 4px 8px;
        }


        /* =========================
           メニューボタン
           ========================= */

        .procSlct {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        .procSlct td {
            border: none;
            padding: 5px;
            text-align: center;
        }

        .procSlct button {
            cursor: pointer;
            padding: 5px 15px;
        }


        /* =========================
           帳票メニュー
           ========================= */

        .report-menu-title {
            text-align: center;
            padding: 15px 0;
            font-size: 18px;
            font-weight: bold;
        }


        /* =========================
           エラーメッセージ
           ========================= */

        #validation-messages {
            min-height: 3.6em;
            margin-bottom: 1em;
        }

        #validation-list {
            color: red;
            margin: 0;
            padding-left: 1.2em;
        }


        /* =========================
           帳票本体
           ========================= */

        .report-area {
            width: 100%;
        }
    </style>
</head>

<body>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <script>
        alert(<?= json_encode($_SESSION['flash_message']) ?>);
    </script>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>


<!-- ==========================================
     上部タイトル・店舗選択
     ========================================== -->

<table class="page-header">
    <tr>

        <td class="page-title">
            コンビニ会計(複数 店舗 事業 対応)
        </td>

        <td class="shop-area">

            <label for="active_shop">
                ようこそ
                <?= htmlspecialchars($_SESSION['user']['username'] ?? 'ゲスト') ?>

                　　現在の操作店舗：
            </label>

            <form
                action="index.php?route=shop.switch"
                method="POST"
                id="shop_selector_form"
                style="display:inline;"
            >

                <select
                    name="active_shop"
                    id="active_shop"
                    onchange="document.getElementById('shop_selector_form').submit();"
                >

                    <?php foreach ($_SESSION['userShops'] as $shop): ?>

                        <option
                            value="<?= h($shop['shop_code']) ?>"
                            <?= ($shop['shop_code'] == $_SESSION['currentShopCode'])
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars(
                                $shop['shop_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                    <option
                        value="   all"
                        <?= ($_SESSION['currentShopCode'] === '   all')
                            ? 'selected'
                            : '' ?>
                    >
                        【全店合算（連結決算）】
                    </option>

                </select>

            </form>

        </td>

    </tr>
</table>


<!-- ==========================================
     操作メニュー
     ========================================== -->

<table class="procSlct">

    <tr>

        <?php foreach ($display_buttons as $key => $label): ?>

            <td>
                <a href="http://test5.local/index.php?route=<?= h($key) ?>">
                    <button type="button">
                        <?= h($label) ?>
                    </button>
                </a>
            </td>

        <?php endforeach; ?>


        <td>
            <a href="http://test5.local/index.php?route=<?= h($RtnRoute) ?>">
                <button type="button">
                    戻る
                </button>
            </a>
        </td>

    </tr>

</table>


<!-- ==========================================
     帳票メニュー
     ========================================== -->

<div class="report-menu-title">
    帳票メニュー
</div>


<!-- ==========================================
     バリデーションメッセージ
     ========================================== -->

<?php

$validationItems = [];

foreach ($this->dto->errData ?? [] as $field => $msg) {

    if (
        is_string($field)
        && (
            strpos($field, 'http://') === 0
            || strpos($field, 'https://') === 0
        )
    ) {
        continue;
    }

    if (!in_array($msg, $validationItems, true)) {
        $validationItems[$field] = $msg;
    }
}

?>

<div id="validation-messages">

    <?php if (!empty($validationItems)): ?>

        <ul id="validation-list">

            <?php foreach ($validationItems as $field => $m): ?>

                <li
                    data-field="<?= h($field) ?>"
                    style="cursor:pointer; text-decoration:underline;"
                >
                    <?= h($m) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    <?php endif; ?>

</div>


<!-- ==========================================
     帳票メニュー・帳票本体
     ========================================== -->

<div class="report-area">

    <?php
    if ($viewType === "sisanhyou") {
        require_once ROOT_PATH . '/views/subView/reportMenu.php';
    }
    ?>

    <?php
    if ($viewType === "sisanhyou") {
        require_once ROOT_PATH . '/views/subView/sisanhyou.php';
    }
    ?>

</div>


</body>
</html>