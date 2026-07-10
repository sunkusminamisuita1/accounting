<?php
?>
<style>
    .TxtBoxLineDel{
        /*テキストボックス枠線なし*/outline: none;
        border: none;
        background: #e7edf8;
    }
    .ProcSlct, .ProcSlct td { border: none !important; }
    .UpdTbl { border-collapse: collapse; width: 100%; table-layout: fixed; border: 1px solid #000000; } /* 幅は中身に合わせるのが一般的 */
    .ProcSlct button { cursor: pointer; padding: 5px 15px; }
    th,td {  padding: 0.6em; border: 1px solid #000000; }

    .button-container {
        display: flex;
        justify-content: space-between; /* 左右に均等配置する */
        width: 100%; /* 必要に応じて幅を指定 */
    }
</style>
<!-- ##############     エラーメッセージ表示    ################ -->
    <?php if (!empty($this->Dto->ErrData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->Dto->ErrData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

<!-- #############     エラーメッセージ POPUP    ############### -->

<h1>勘定科目ー定義　編集・削除</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <script>
      alert(<?= json_encode($_SESSION['flash_message']) ?>);
    </script>
<?php unset($_SESSION['flash_message']); endif; ?>

    <?php
        require_once ROOT_PATH . '/views/lib/ProcSlct.php';
    ?>
    <?php if (!empty($this->Dto->ErrData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->Dto->ErrData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- ##############     エラーメッセージ表示    ################ -->
    <?=  $this->CtrErrMsgPopUp->Show($this->Dto);  ?>
    <hr>
    <h3>下表の店舗情報を追加・修正・削除<br><br>
    <div style="text-align: center;">表の修正完了後、修正実行ボタンを押してください。</div>
    </h3>
    <form method="POST" action="index.php?route=shops.edit">
        <div style="text-align: center;" >
            <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
            <button name="ShopsPfm" type="submit"
                onclick="return confirm
                    ('店舗情報 修正欄の内容をデータベースに登録します。\n本当に変更してもよろしいですか？');"  
                    value="<?= h('修正実行') ?>" >修正実行
            </button>
            <button name="ShopsPfm" type="submit"
                onclick="return confirm
                    ('店舗情報 修正欄の内容を、もとに戻します。\nよろしいですか？');"  
                    value="<?= h('キャンセル') ?>" >キャンセル
            </button>
        </div>

        <table class="UpdTbl" >

            <tbody>
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <div>これは左側です。</div>

                            <table class="UpdTbl">
                                <tbody>
                                    <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                        <th style="width: 5%;" >店舗番号</th>
                                        <th style="width: 8%;" >店舗名</th>
                                        <th style="width: 13%;" >開業日</th>
                                        <th style="width: 8%;" >備考</th>
                                        <th style="width: 8%;" >閉店</th>
                                        <th style="width: 8%;" >閉店日</th>
                                        <th style="width: 25%;" >

                                                <button name="ShopsPfm" type="submit" value="<?= h('追加') ?>" >行追加</button>
                                                <br><hr>
                                                エラーメッセージ
                                        </th>
                                        <th style="width: 6%;" >
                                            <button name="ShopsPfm" type="submit" value="<?= h('削除') ?>" >削除</button>
                                        </th>
                                    </tr>
                                    <?php foreach ($ShopList as $Key => $Row): ?>
                                        <input type="hidden" name="ViewEditKey" value="<?= h($Key) ?>">
                                        <tr style="background-color: #ffffff; font-weight: bold; text-align: center;">

                                            <td>                           <!--   店舗番号　pri-key   -->
                                                <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    type="text" name="ShopsUpdDt[<?= $Key ?>][shop_code]"
                                                    value="<?= h($Row['shop_code']) ?? '' ?>" readonly>
                                            </td>

                                            <td style="text-align: left;">  <!--   店舗名称   -->
                                                <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    type="text" name="ShopsUpdDt[<?= $Key ?>][shop_name]"
                                                    value="<?= h($Row['shop_name']) ?? '' ?>" readonly>
                                            </td>

                                            <td style="text-align: left;">  <!--   開業日   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" 
                                                    type="text" name="shopsUpdDt[<?= $Key ?>][open_date]" 
                                                    value="<?= h($Row['open_date']) ?? '' ?>">
                                            </td>

                                            <td style="text-align: left;">  <!--   摘要   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" 
                                                    type="text" name="shopsUpdDt[<?= $Key ?>][summry]" 
                                                    value="<?= h($Row['summary']) ?? '' ?>">

                                            </td>

                                            <td>                            <!--   閉店チェックボックス   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" type="checkbox" 
                                                    name="ShopsUpdDt[<?= $Key ?>][closed]" value="On"
                                                    <?php if (isset($Row['closed']) && $Row['closed'] === 1 ) { 
                                                        echo 'checked'; } ?>
                                                >
                                            </td>
                                            
                                            <td style="font-color: #ff0000;">    <!--   エラーメッセージ   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" type="text" 
                                                    name="ShopsUpdDt[<?= $Key ?>][errmsg]"
                                                    value="<?= h($Row['errmsg'] ?? '')  ?>" readonly>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>



                    </td>

                    <td style="width: 50%; vertical-align: top;">
                        <div>これは右側です。</div>



                        ここには修正した勘定科目が損益計算書、貸借対象表のどの位置に追加修正されたか確認できるようにする




                    </td>
                </tr>
            </tbody>
        </table>
    </form>  <!--###############################################-->

