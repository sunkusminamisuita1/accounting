<?php
?>
<style>
    .txtBoxLineDel{
        /*テキストボックス枠線なし*/outline: none;
        border: none;
        background: #e7edf8; 
    }
    .procSlct, .procSlct td { border: none !important; }
    .UpdTbl { border-collapse: collapse; width: 100%; table-layout: fixed; border: 1px solid #000000; } /* 幅は中身に合わせるのが一般的 */
    .procSlct button { cursor: pointer; padding: 5px 15px; }
    th,td {  padding: 0.6em; border: 1px solid #000000; }

    .button-container {
        display: flex;
        justify-content: space-between; /* 左右に均等配置する */
        width: 100%; /* 必要に応じて幅を指定 */
    }
</style>
<!-- ##############     エラーメッセージ表示    ################ -->
    <!-- <?php if (!empty($this->dto->errData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->dto->errData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?> -->

<!-- #############     エラーメッセージ POPUP    ############### -->

<h1>店舗情報　登録・修正・閉店処理</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <script>
      alert(<?= json_encode($_SESSION['flash_message']) ?>);
    </script>
<?php unset($_SESSION['flash_message']); endif; ?>

    <?php
        require_once ROOT_PATH . '/views/procSlct.php';
    ?>
    <?php if (!empty($this->dto->errData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->dto->errData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- ##############     エラーメッセージ表示    ################ -->
    <?=  $this->ctrerrMsgPopUp->Show($this->dto);  ?>
    <hr>
    <form method="POST" action="index.php?route=shop.edit">

        <table class="UpdTbl" >

            <tbody>
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <h3>店舗情報新規登録</h3>
                        店舗情報を登録　入力完了後 登録ボタンを押してください。
                        <div style="text-align: center;" >
                            <button name="shopsPfm" type="submit"
                                onclick="return confirm
                                ('店舗情報 をデータベースに登録します。\n本当に変更してもよろしいですか？');"  
                                value="<?= h('登録実行') ?>" >登録実行
                            </button>
                        </div>
                        <br>
                        <table class="UpdTbl">
                                <tbody>
                                    <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                        <th style="width: 8%;" >店舗番号</th>
                                        <th style="width: 8%;" >店舗名</th>
                                        <th style="width: 8%;" >開業日</th>
                                        <th style="width: 13%;" >備考</th>
                                        <th style="width: 25%;"></th>
                                    </tr>
                                    <tr style="background-color: #ffffff; font-weight: bold; text-align: center;">

                                        <td>                           <!--   店舗番号   -->
                                            <input class="txtBoxLineDel" style="width: 90%; text-align: center;" 
                                                maxlength="6" 
                                                inputmode="numeric" 
                                                pattern="[0-9]{6}" 
                                                placeholder="例: 000001" 
                                                type="text" name="newShopCode"
                                                value="<?= h($newShopsCode ?? '' )?>" >
                                        </td>

                                        <td style="text-align: left;">  <!--   店舗名称   -->
                                            <input class="txtBoxLineDel" style="width: 90%; text-align: center;" 
                                                type="text" name="newShopName"
                                                value="<?= h($newShopName ?? '' ) ?>">
                                        </td>

                                        <td style="text-align: left;">  <!--   開業日   -->
                                            <input class="txtBoxLineDel" style="width: 90%;" 
                                                type="text" name="newOpenDate" 
                                                value="<?= h($newOpenDate ?? '' ) ?>">
                                        </td>

                                        <td style="text-align: left;">  <!--   摘要   -->
                                            <input class="txtBoxLineDel" style="width: 90%;" 
                                                type="text" name="newSummary" 
                                                value="<?= h($newSummary ?? '' ) ?>">

                                        </td>

                                        <td style="font-color: #ff0000;">    <!--   エラーメッセージ   -->
                                            <input class="txtBoxLineDel" style="width: 90%;" type="text" 
                                                name="newErrmsg"
                                                value="<?= h($newErrMsg ?? '' )  ?>" >
                                        </td>
                                    </tr>
                            </tbody>
                    </table>

                        <br>
                        <br>
                        <hr>
                        <h3>店舗情報を修正・閉店登録(店番,店舗名の修正はできません。)</h3>
                        <div style="text-align: center;" >
                            <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
                            <!-- <button name="shopsPfm" type="submit"
                                onclick="return confirm
                                ('店舗情報 修正欄の内容の正当性チェックを行います。\nデータベースの更新は行いません。？');"  
                                value="<?= h('チェック') ?>" >相関チェック
                            </button> -->
                            <button name="shopsPfm" type="submit"
                                onclick="return confirm
                                ('店舗情報 修正欄の内容をデータベースに登録します。\n本当に変更してもよろしいですか？');"  
                                value="<?= h('修正実行') ?>" >修正実行
                            </button>
                            <button name="shopsPfm" type="submit"
                                onclick="return confirm
                                ('店舗情報 修正欄の内容を、もとに戻します。\nよろしいですか？');"  
                                value="<?= h('キャンセル') ?>" >キャンセル
                            </button>
                        </div>
                        <br>

                            <table class="UpdTbl">
                                <tbody>
                                    <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                        <th style="width: 8%;" >店舗番号</th>
                                        <th style="width: 8%;" >店舗名</th>
                                        <th style="width: 8%;" >開業日</th>
                                        <th style="width: 13%;" >備考</th>
                                        <th style="width: 8%;" >閉店</th>
                                        <th style="width: 8%;" >閉店日</th>
                                        <th style="width: 25%;" >

                                                <!-- <button name="shopsPfm" type="submit" value="<?= h('追加') ?>" >行追加</button>
                                                <br><hr> -->
                                                エラーメッセージ
                                        </th>
                                        <th style="width: 6%;">行削除</th>

                                        <?php /*
                                        <th style="width: 6%;" >
                                            <button name="shopsPfm" type="submit" value="<?= h('削除') ?>" >削除</button>
                                        </th>
                                        */?>
                                    </tr>
                                    <?php foreach ($ShopList as $Key => $row): ?>
                                        <input type="hidden" name="viewEditKey" value="<?= h($Key) ?>">
                                        <tr style="background-color: #ffffff; font-weight: bold; text-align: center;">

                                            <td>                           <!--   店舗番号　pri-key   -->
                                                <input class="txtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    maxlength="6" 
                                                    inputmode="numeric" 
                                                    pattern="[0-9]{6}" 
                                                    placeholder="例: 000001" 
                                                    type="text" name="shopsUpdDt[<?= $Key ?>][shop_code]"
                                                    value="<?= h($row['shop_code']) ?? '' ?>" readonly >
                                            </td>

                                            <td style="text-align: left;">  <!--   店舗名称   -->
                                                <input class="txtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    type="text" name="shopsUpdDt[<?= $Key ?>][shop_name]"
                                                    value="<?= h($row['shop_name']) ?? '' ?>" readonly>
                                            </td>

                                            <td style="text-align: left;">  <!--   開業日   -->
                                                <input class="txtBoxLineDel" style="width: 90%;" 
                                                    type="text" name="shopsUpdDt[<?= $Key ?>][open_date]" 
                                                    value="<?= h($row['open_date']) ?? '' ?>" <?= $isLocked??'' ?> 
                                                >
                                            </td>

                                            <td style="text-align: left;">  <!--   摘要   -->
                                                <input class="txtBoxLineDel" style="width: 90%;" 
                                                    type="text" name="shopsUpdDt[<?= $Key ?>][summary]" 
                                                    value="<?= h($row['summary']) ?? '' ?>"  <?= $isLocked??'' ?> 
                                                >

                                            </td>

                                            <td>                            <!--   閉店チェックボックス   -->
                                                <?php
                                                    $checked = '';
                                                    if (!empty($row['closed']) || 
                                                       (!empty($_POST['shopsUpdDt'][$Key]['closed']) && 
                                                       $_POST['shopsUpdDt'][$Key]['closed'] === '1')) {
                                                            $checked = 'checked';
                                                    }
                                                ?>
                                                <input class="txtBoxLineDel" style="width: 90%;" type="checkbox" 
                                                    name="shopsUpdDt[<?= $Key ?>][closed]" value="1"
                                                    <?= $checked ?>
                                                    <?= $isLocked??'' ?> 
                                                >
                                            </td>

                                            <td>                            <!--   閉店日   -->
                                                <input class="txtBoxLineDel" style="width: 90%;" type="text" 
                                                    name="shopsUpdDt[<?= $Key ?>][closed_date]" 
                                                    value="<?= h($row['closed_date']) ?? '' ?>"  <?= $isLocked??'' ?>
                                                >
                                            </td>                                          

                                            <td style="font-color: #ff0000;">    <!--   エラーメッセージ   -->
                                                <input class="txtBoxLineDel" style="width: 90%;" type="text" 
                                                    name="shopsUpdDt[<?= $Key ?>][errmsg]"
                                                    value="<?= h($row['errmsg'] ?? '')  ?>" readonly>
                                            </td>
                            

                                            <td>                            <!--   行削除チェックボックス   -->     
                                                <?php
                                                    $checked = '';
                                                    if (!empty($row['deleted']) || 
                                                       (!empty($_POST['shopsUpdDt'][$Key]['deleted']) && 
                                                       $_POST['shopsUpdDt'][$Key]['deleted'] === '1')) {
                                                            $checked = 'checked';
                                                    }
                                                ?>
                                                <input class="txtBoxLineDel" style="width: 90%;" type="checkbox" 
                                                    name="shopsUpdDt[<?= $Key ?>][deleted]" value="1"
                                                    <?= $checked ?>
                                                   <?= $isLocked??'' ?>
                                                >
                                            </td>
                                            
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                    </td>

                    <td style="width: 50%; vertical-align: top;">
                        <div>これは右側です。</div>

                        ここには　マニュアル　helpを作成

                    </td>
                </tr>
            </tbody>
        </table>
    </form>  <!--###############################################-->