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

    <?=  $this->CtrErrMsgPopUp->Show($this->CtrDto);  ?>
<h1>勘定科目ー定義　編集・削除</h1>

<?php if (!empty($_SESSION['flash_message'])): ?>
    <script>
      alert(<?= json_encode($_SESSION['flash_message']) ?>);
    </script>
<?php unset($_SESSION['flash_message']); endif; ?>

    <?php
        require_once ROOT_PATH . '/views/lib/ProcSlct.php';
    ?>
    <?php if (!empty($this->CtrDto->ErrData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->CtrDto->ErrData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- ##############     エラーメッセージ表示    ################ -->
    <?=  $this->CtrErrMsgPopUp->Show($this->CtrDto);  ?>
    <hr>
    <h3>下表の勘定科目を追加・修正・削除<br><br>
    <div style="text-align: center;">表の修正完了後、修正実行ボタンを押してください。</div>
    </h3>
    <form method="POST" action="index.php?route=accounts.edit">
        <div style="text-align: center;" >
            <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
            <button name="AcctPfm" type="submit"
                onclick="return confirm
                    ('勘定科目 修正欄の内容をデータベースに登録します。\n本当に変更してもよろしいですか？');"  
                    value="<?= h('修正実行') ?>" >修正実行
            </button>
            <button name="AcctPfm" type="submit"
                onclick="return confirm
                    ('勘定科目 修正欄の内容を、もとに戻します。\nよろしいですか？');"  
                    value="<?= h('キャンセル') ?>" >キャンセル
            </button>
        </div>
        <!--</form>$$$$$$$$$$$$$$$$$$$$$$$-->

        <table class="UpdTbl" >

            <tbody>
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <div>これは左側です。</div>

                        <!--<form method="POST" action="index.php?route=accounts.edit">$$$$$$$$$$$$$$$$$$$$$$-->
                            <!--<input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">-->
                            <table class="UpdTbl">
                                <tbody>
                                    <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                        <th style="width: 5%;" >ID</th>
                                        <th style="width: 8%;" >ユーザーID</th>
                                        <th style="width: 13%;" > 勘定科目</th>
                                        <th style="width: 8%;" >貸借種別</th>
                                        <th style="width: 25%;" >

                                                <button name="AcctPfm" type="submit" value="<?= h('追加') ?>" >行追加</button>
                                                <br><hr>
                                                エラーメッセージ
                                        </th>
                                        <th style="width: 6%;" >
                                            <button name="AcctPfm" type="submit" value="<?= h('削除') ?>" >削除</button>
                                        </th>
                                    </tr>
                                    <?php foreach ($Accounts as $Key => $Row): ?>
                                        <input type="hidden" name="ViewEditKey" value="<?= h($Key) ?>">
                                        <tr style="background-color: #ffffff; font-weight: bold; text-align: center;">
                                            <td>                           <!--   行番号　pri-key   -->
                                                <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    type="text" name="AcctUpdDt[<?= $Key ?>][id]"
                                                    value="<?= h($Row['id']) ?? '' ?>" readonly>
                                            </td>
                                            <td style="text-align: left;">  <!--   ユーザーID   -->
                                                <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    type="text" name="AcctUpdDt[<?= $Key ?>][user_id]"
                                                    value="<?= h($Row['user_id']) ?? '' ?>" readonly>
                                            </td>
                                            <td style="text-align: left;">  <!--   勘定科目名   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" 
                                                    type="text" name="AcctUpdDt[<?= $Key ?>][name]" 
                                                    value="<?= h($Row['name']) ?? '' ?>">
                                            </td>
                                            <td>                            <!--   勘定科目種別   -->
                                                <select style="width: 95%;" name="AcctUpdDt[<?= $Key ?>][type]" required>
                                                    <option value="">選択してください</option>
    
                                                    <?php foreach($this->CtrDto->AccountsType as $i => $a): ?>
                                                        <option value="<?= h($a) ?>" 
                                                            <?= (isset($Row['type']) && $Row['type'] == $a) ? 'selected' : '' ?>>
                                                            <?= h($a) ?> 
                                                        </option>
                                                    <?php endforeach; ?>

                                                </select>
                                                <!-- <input class="TxtBoxLineDel" style="width: 90%; text-align: center;" 
                                                    type="hidden" name="AcctUpdDt[<?php //echo $Key ?>][type]" 
                                                    value="<?//php echo h($Row['type']) ?? '' ?>"
                                                > -->
                                            </td>
                                            <td style="font-color: #ff0000;">    <!--   エラーメッセージ   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" type="text" 
                                                    name="AcctUpdDt[<?= $Key ?>][errmsg]"
                                                    value="<?= h($Row['errmsg'] ?? '')  ?>" readonly>
                                            </td>
                                            <td>                            <!--   削除チェックボックス   -->
                                                <input class="TxtBoxLineDel" style="width: 90%;" type="checkbox" 
                                                    name="AcctUpdDt[<?= $Key ?>][del]" value="On"
                                                    <?php if (isset($Row['edittype']) && $Row['edittype'] === '削除') { 
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



                        ここには修正した勘定科目が損益計算書、貸借対象表のどの位置に追加修正されたか確認できるようにする




                    </td>
                </tr>
            </tbody>
        </table>
    </form>  <!--###############################################-->

