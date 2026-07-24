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

<h1>店舗情報　登録・修正・閉店処理</h1>

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
    <form method="POST" action="index.php?route=shop.edit">

        <table class="UpdTbl" >

            <tbody>
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <h3>店舗情報新規登録</h3>
                        店舗情報を登録　入力完了後 登録ボタンを押してください。
                        <div style="text-align: center;" >
                            <button name="ShopsPfm" type="submit"
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
                                            <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                maxlength="6" 
                                                inputmode="numeric" 
                                                pattern="[0-9]{6}" 
                                                placeholder="例: 000001" 
                                                type="text" name="NewShopCode"
                                                value="<?= h($NewShopsCode ?? '' )?>" >
                                        </td>

                                        <td style="text-align: left;">  <!--   店舗名称   -->
                                            <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                type="text" name="NewShopName"
                                                value="<?= h($NewShopName ?? '' ) ?>">
                                        </td>

                                        <td style="text-align: left;">  <!--   開業日   -->
                                            <input class="TxtBoxLineDel" style="width: 90%;" 
                                                type="text" name="NewOpenDate" 
                                                value="<?= h($NewOpenDate ?? '' ) ?>">
                                        </td>

                                        <td style="text-align: left;">  <!--   摘要   -->
                                            <input class="TxtBoxLineDel" style="width: 90%;" 
                                                type="text" name="NewSummary" 
                                                value="<?= h($NewSummary ?? '' ) ?>">

                                        </td>

                                        <td style="font-color: #ff0000;">    <!--   エラーメッセージ   -->
                                            <input class="TxtBoxLineDel" style="width: 90%;" type="text" 
                                                name="NewErrmsg"
                                                value="<?= h($NewErrMsg ?? '' )  ?>" >
                                        </td>
                                    </tr>
                            </tbody>
                    </table>

                        <br>
                        <br>
                        <hr>
                        <h3>店舗情報を修正・閉店登録(店番,店舗名の修正はできません。)</h3>
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

                                                <!-- <button name="ShopsPfm" type="submit" value="<?= h('追加') ?>" >行追加</button>
                                                <br><hr> -->
                                                エラーメッセージ
                                        </th>
                                        <th style="width: 6%;">行削除</th>

                                        <?php /*
                                        <th style="width: 6%;" >
                                            <button name="ShopsPfm" type="submit" value="<?= h('削除') ?>" >削除</button>
                                        </th>
                                        */?>
                                    </tr>
                                    <?php foreach ($ShopList as $Key => $Row): ?>
                                        <input type="hidden" name="ViewEditKey" value="<?= h($Key) ?>">
                                        <tr style="background-color: #ffffff; font-weight: bold; text-align: center;">

                                            <td>                           <!--   店舗番号　pri-key   -->
                                                <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    maxlength="6" 
                                                    inputmode="numeric" 
                                                    pattern="[0-9]{6}" 
                                                    placeholder="例: 000001" 
                                                    type="text" name="ShopsUpdDt[<?= $Key ?>][shop_code]"
                                                    value="<?= h($Row['shop_code']) ?? '' ?>" readonly >
                                            </td>

                                            <td style="text-align: left;">  <!--   店舗名称   -->
                                                <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    type="text" name="ShopsUpdDt[<?= $Key ?>][shop_name]"
                                                    value="<?= h($Row['shop_name']) ?? '' ?>" readonly>
                                            </td>

                                            <td style="text-align: left;">  <!--   開業日   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" 
                                                    type="text" name="ShopsUpdDt[<?= $Key ?>][open_date]" 
                                                    value="<?= h($Row['open_date']) ?? '' ?>">
                                            </td>

                                            <td style="text-align: left;">  <!--   摘要   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" 
                                                    type="text" name="ShopsUpdDt[<?= $Key ?>][summary]" 
                                                    value="<?= h($Row['summary']) ?? '' ?>">

                                            </td>

                                            <td>                            <!--   閉店チェックボックス   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" type="checkbox" 
                                                    name="ShopsUpdDt[<?= $Key ?>][closed]" value="unchecked"
                                                    <?php if (isset($Row['closed']) && $Row['closed'] === 1 ) { 
                                                        echo 'checked'; } ?>
                                                >
                                            </td>

                                            <td>                            <!--   閉店日   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" type="text" 
                                                    name="ShopsUpdDt[<?= $Key ?>][closed_date]" 
                                                    value="<?= h($Row['closed_date']) ?? '' ?>"
                                                >
                                            </td>                                          

                                            <td style="font-color: #ff0000;">    <!--   エラーメッセージ   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" type="text" 
                                                    name="ShopsUpdDt[<?= $Key ?>][errmsg]"
                                                    value="<?= h($Row['errmsg'] ?? '')  ?>" readonly>
                                            </td>
                            

                                            <td>                            <!--   行削除チェックボックス   -->     
                                                 
                                                <input class="TxtBoxLineDel" style="width: 90%;" type="checkbox" 
                                                    name="ShopsUpdDt[<?= $Key ?>][delete]" value="unchecked"
                                                    <?php if (isset($Row['delete']) && $Row['delete'] === 1 ) { 
                                                        echo 'checked'; } ?>
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