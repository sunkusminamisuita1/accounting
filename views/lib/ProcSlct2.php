<table class="ProcSlct">
    <tr>
        <!-- ★ボタンの総数（配列の件数 + 戻るボタンの1）をカウントして、自動で列を結合します -->
        <td colspan="<?= count($display_buttons) + 1; ?>" style="text-align: center; padding-bottom: 15px;">

            <div class="shop-selector-container" style="display: inline-block; text-align: left;">
                <label for="active_shop">現在の操作店舗：</label>
                <!-- フォームを配置し、methodをpostにする -->
                <form action="index.php?route=shop.switch" method="POST" id="shop_selector_form" style="display: inline;">
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
    </tr>

    <tr>
        <?php foreach  ($display_buttons as $key => $label): ?>
            <td>
                <a href="http://test5.local/index.php?route=<?= h($key) ?>">
                    <button type="button"><?= h($label) ?></button>
                </a>
            </td>
        <?php endforeach; ?>
        <td>
            <a href="http://test5.local/index.php?route=<?= h($RtnRoute) ?>">
                <button type="button"><?= h('戻る') ?></button>
            </a>
        </td>
    </tr>
</table>