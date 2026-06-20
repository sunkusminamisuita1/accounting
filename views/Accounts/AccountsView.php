<style>
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
<table class="UpdTbl">
<tr>
<td style="width: 50%; vertical-align: top;">
    <h1>勘定科目　追加　修正</h1>

    




<?php if (!empty($_SESSION['flash_message'])): ?>
    <script>
      alert(<?= json_encode($_SESSION['flash_message']) ?>);
    </script>
<?php unset($_SESSION['flash_message']); endif; ?>





    <?php
        require_once ROOT_PATH . '/views/lib/ProcSlct.php';
        $details = $this->Dto->DtoDetails;
    ?>
    <?php if (!empty($this->Dto->ErrData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->Dto->ErrData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- ##############     エラーメッセージ表示    ################ -->
    <?=  $this->ErrMsgPopUp->Show($this->Dto);  ?>
    <br><hr>
    <h3>単独検索(伝票No，取引日付，取引金額，摘要欄あいまい検索)は、<br>
    １つ以上の検索条件を入力して検索ボタンを押してください。
    </h3>


    <form method="POST" action="index.php?route=Accounts.add">
        <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
        <table class="UpdTbl">
            <tr>
                <td>
                    伝票No
                </td>

                <td>
                    <input type="text" name="ListVcrNum" value="<?= h($this->Dto->ListVcrNum ?? '') ?>">
                </td>

            <tr>　
            </tr>

            <tr>
                <td>
                    日付        
                </td>

                <td>
                    <input type="date" name="ListVcrDate" value="<?= h($this->Dto->Date ?? '') ?>">
                </td>
            </tr>

            <tr>
                <td>
                    期間        
                </td>
                <td>
                    開始<input type="date" name="LstVcrSearchStartDate" value="<?= h($this->VcrListDatePeriod['検索開始日付'] ?? '') ?>">
                
                
                    　　　終了<input type="date" name="LstVcrSearchEndDate" value="<?= h($this->VcrListDatePeriod['検索終了日付'] ?? '') ?>">
                </td>
            </tr>

            <tr>　
            </tr>

            <tr>
                <td>
                    摘要（曖昧検索）
                </td>

                <td>
                    <input type="text" name="ListVcrSummary" value="<?= h($this->Dto->Summary ?? '') ?>">
                </td>
            </tr>
        </table>
        <br>
        <button name="SimpleSearch" type="submit" value="<?= h('SimpleSearch') ?>">検索</button>
    </form>

    
    <hr>




<!--　##################　仕分け伝票　修正　エリア  ###################-->
    <div>
            <h3>仕分け伝票修正エリア</h3>
    </div>
        <form method="POST" action="index.php?route=voucher.list">
            <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
            <input type="hidden" name="JdId" value="<?= h($TokenKey) ?>">
            <table class="UpdTbl">
                <?php $VcrIdSW = 0; $VcrSearchedData = $this->Dto->VcrSearchedData;
                        $CreditAmount = 0; $DebitAmount = 0; $CreditName = ''; $DebitName = '';
                ?>
                <?php foreach ($VcrSearchedData as $VcrRowNo => $Row):  ?>
                    <?php if($Row['side'] === 'credit') {
                        $CreditAmount = (int)$Row['amount']??'0';
                        $CreditName = $Row['name']??'';
                    } else {
                        $DebitAmount = (int)$Row['amount']??'0';
                        $DebitName = $Row['name']??'';
                    }
                    ?>
                    <?php if ($VcrIdSW != $Row['voucher_id']): ?>
                            <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                <th style=" width: 5%;" >伝票No</th>
                                <th style=" width: 11%;" >日付</th>
                                <th style=" width: 13%;" >借方科目</th>
                                <th style=" width: 10%;">借方金額</th>
                                <th style=" width: 10%;">貸方金額</th>
                                <th style=" width: 13%;">貸方科目</th>
                                <th style=" width: 15%;">摘要<br>
                                    <input style="width : 95%;" type="text" name="VcrUpdDt[<?= $VcrRowNo ?>][summary]" 
                                        value="<?= h($Row['summary']) ?? '' ?>"
                                    >                                    
                                </th>
                                <th style=" width: 22%;">
                                    <button name="VcrUpdate" type="submit"
                                        onclick="return confirm('伝票修正欄の内容をデータベースに登録します。元に戻せません。\n本当に変更してもよろしいですか？');" 
                                        class="btn btn-danger"
                                        value="<?= h('VcrUpdate') ?>">修正実行
                                    <button name="VcrDelete" type="submit" 
                                        onclick="return confirm('この伝票を削除すると、紐づく明細データもすべて削除されます。\n本当に削除してもよろしいですか？');" 
                                        class="btn btn-danger"
                                        value="<?= h('VcrDelete') ?>">伝票削除
                                    </button>
                                </th>
                            </tr>
                    <?php endif; ?>
                    <tr>
                    <?php if ($VcrIdSW  != $Row['voucher_id']): ?>
                        <?php $VcrIdSW   = (int)$Row['voucher_id']; ?>
                            <td  style="font-weight: bold; text-align: center;">
                                <?= h($Row['voucher_id']) ?>
                            </td>
                            <td style="font-weight: bold; text-align: center;">
                                <?= h($Row['voucher_date']??'') ?>
                            </td>
                    <?php else: ?>
                            <td></td>
                            <td></td>
                    <?php endif; ?>
                            <td>
                                <?php if($Row['side'] === 'debit'): ?>
                                    <select  style=" width: 95%;"  name="VcrUpdDt[<?= $VcrRowNo ?>][account_id]" required >
                                        <option value="">選択してください</option>
                                            <?php foreach($this->Dto->Accounts as $a): ?>
                                                <option value="<?= h($a['id']) ?>" 
                                                    <?= (isset($Row['account_id']) && $Row['account_id'] == $a['id']) ? 'selected' : '' ?>>
                                                    <?= h($a['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="VcrUpdDt[<?= $VcrRowNo ?>][DebitName]" value="<?= h($DebitName ?? '') ?>">
                                <?php endif; ?>
                            </td>
                            <td  style="font-weight: bold; text-align: right;">
                                <?php if($Row['side'] === 'debit'): ?>
                                    <input style="width : 95%;" type="text" name="VcrUpdDt[<?= $VcrRowNo ?>][amount]" value="<?= h($Row['amount']) ?? '' ?>">
                                <?php endif; ?>
                            </td>
                            <td  style="font-weight: bold; text-align: right;">
                                <?php if($Row['side'] === 'credit'): ?>
                                    <input style="width : 95%;" type="text" name="VcrUpdDt[<?= $VcrRowNo ?>][amount]" value="<?= h($Row['amount']) ?? '' ?>">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($Row['side'] === 'credit'): ?>
                                    <select style=" width: 95%;" name="VcrUpdDt[<?= $VcrRowNo ?>][account_id]" required >
                                        <option value="">選択してください</option>
                                            <?php foreach($this->Dto->Accounts as $a): ?>
                                                <option value="<?= h($a['id']) ?>" 
                                                    <?= (isset($Row['account_id']) && $Row['account_id'] == $a['id']) ? 'selected' : '' ?>>
                                                    <?= h($a['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="VcrUpdDt[<?= $VcrRowNo ?>][CreditName]" value="<?= h($CreditName ?? '') ?>">
                                <?php endif; ?>                           
                            </td>
                            <td  style="font-weight: bold; text-align: center;">



                                    <input style="width : 95%;" type="text" name="VcrUpdDt[<?= $VcrRowNo ?>][jd_summary]" 
                                        value="<?= h($Row['jd_summary'] ?? '') ?>"
                                    >                                    


                                <!-- <?php //echo  h($Row['summary']??'') ?> -->
                            </td>
                            <td>
                              <div class="button-container">
                                <input type="hidden" name="VcrUpdDt[<?= $VcrRowNo ?>][JdId]" value="<?= h($Row['JdId'] ?? 0) ?>">
                                <input type="hidden" name="VcrUpdDt[<?= $VcrRowNo ?>][id]" value="<?= h($Row['id'] ?? '') ?>">
                                <input type="hidden" name="VcrUpdDt[<?= $VcrRowNo ?>][voucher_id]" value="<?= h($Row['voucher_id'] ?? '') ?>">
                                <input type="hidden" name="VcrUpdDt[<?= $VcrRowNo ?>][side]" value="<?= h($Row['side'] ?? '') ?>">
                                <button name="VcrAddDebit" type="submit" value="<?= h($VcrRowNo ?? '') ?>">借方行追加</button>
                                <button name="VcrAddCredit" type="submit" value="<?= h($VcrRowNo ?? '') ?>">貸方行追加</button>
                                <button name="VcrDetailLineDel" type="submit" value="<?= h($VcrRowNo ?? '') ?>">行削除</button>
                              </div>    
                            </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </form>
</td>

<!--　##################　検索結果表示エリア  ###################-->
<td style="width: 50%; vertical-align: top;">
    <h3>検索結果</h3>
        <form method="POST" action="index.php?route=voucher.list">
            <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
            <table class="UpdTbl">
                <?php if (empty($this->Dto->VcrListResult)): ?>
                    <tr>
                        <td colspan="9">検索条件に一致する伝票が見つかりませんでした。</td>
                    </tr>
                <?php endif; ?>
                <?php (int)$VcrIdSW = 0; $VcrListResult = $this->Dto->VcrListResult; ?>
                <?php foreach ($VcrListResult as $VcrId => $Row): $CreditAmount = 0; $DebitAmount = 0; $CreditName = ''; $DebitName = ''; ?>
                    <?php if($Row['side'] === 'credit') {
                        $CreditAmount = (int)$Row['amount']??'0';
                        $CreditName = $Row['name']??'';
                    } else {
                        $DebitAmount = (int)$Row['amount']??'0';
                        $DebitName = $Row['name']??'';
                    }
                    ?>
                    <?php if ($VcrIdSW !== (int)$Row['voucher_id']): ?>
                            <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                <th width 5%>伝票No</th>
                                <th width 8%>日付</th>
                                <th>借方科目</th>
                                <th>借方金額</th>
                                <th>貸方金額</th>
                                <th>貸方科目</th>
                                <th>摘要<br>
                                    <?= h($Row['summary'] )?>                                    
                                </th>
                                <th>
                                    <?php if($this->Dto->VcrListResult[$VcrId]['voucher_id'] !== '999999999999'): ?>
                                         <button name="VcrUpdateNo" type="submit" value="<?= h($Row['voucher_id']) ?>">修正</button>
                                    <?php endif; ?>
                                </th>
                            </tr>
                    <?php endif; ?>
                        <tr>
                    <?php if (!empty($Row['JdId'])): ?>
                        <?php if ((int)$VcrIdSW !== (int)$Row['voucher_id']): ?>
                            <?php (int)$VcrIdSW = (int)$Row['voucher_id']; ?>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($Row['voucher_id']) ?>
                                </td>
                                <td style="font-weight: bold; text-align: center;">
                                    <?= h($Row['voucher_date']??'') ?>
                                </td>
                        <?php else: ?>
                                <td></td>
                                <td></td>
                        <?php endif; ?>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($DebitName) ?>
                                </td>
                                <td  style="font-weight: bold; text-align: right;">
                                    <?= h($DebitAmount) ?>
                                </td>
                                <td  style="font-weight: bold; text-align: right;">
                                    <?= h($CreditAmount) ?>
                                </td>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($CreditName) ?>
                                </td>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($Row['jd_summary']??'') ?>
                                </td>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($Row['total_debit']??'') ?>
                                </td>
                    <?php else: ?>
<!--                                <td></td>
                                <td></td>
                                <td style="font-weight: bold; text-align: center;">
                                    合計</td>
                                <td style="font-weight: bold; text-align: right;">
                                    <?= h($Row['debit_total']??'') ?>
                                </td>
                                <td style="font-weight: bold; text-align: right;">
                                    <?= h($Row['credit_total']??'') ?>
                                </td>
                                <td></td>
                                <td style="font-weight: bold; text-align: right;">
                                    ステータス
                                </td>
                                <td style="color: #ff0073; font-weight: bold; text-align: center;">
                                    <?= h($Row['credit_total']??'') === h($Row['debit_total']??'') ? ' ': '貸借不一致' ?>
-->                                </td>
                    <?php endif; ?>
                            </tr>
                <?php endforeach; ?>
            </table>
        </form>
</td>
</tr>


</table>