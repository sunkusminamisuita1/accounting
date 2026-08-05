<style>
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
<table class="UpdTbl">
<tr>
<td style="width: 50%; vertical-align: top;">
    <h1>仕分伝票修正-対象伝票検索</h1>

    




<?php if (!empty($_SESSION['flash_message'])): ?>
    <script>
      alert(<?= json_encode($_SESSION['flash_message']) ?>);
    </script>
<?php unset($_SESSION['flash_message']); endif; ?>





    <?php
        require_once ROOT_PATH . '/views/lib/procSlct.php';
        $details = $this->dto->dtoDetails;
    ?>
    <?php if (!empty($this->dto->errData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->dto->errData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- ##############     エラーメッセージ表示    ################ -->
    <?=  $this->errMsgPopUp->Show($this->dto);  ?>
    <br><hr>
    <h3>単独検索(伝票No，取引日付，取引金額，摘要欄あいまい検索)は、<br>
    １つ以上の検索条件を入力して検索ボタンを押してください。
    </h3>
    <form method="POST" action="index.php?route=voucher.list">
        <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
        <table class="UpdTbl">
            <tr>
                <td>
                    伝票No
                </td>

                <td>
                    <input type="text" name="listVcrNum" value="<?= h($this->dto->listVcrNum ?? '') ?>">
                </td>

            <tr>　
            </tr>

            <tr>
                <td>
                    日付        
                </td>

                <td>
                    <input type="date" name="listVcrDate" value="<?= h($this->dto->Date ?? '') ?>">
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
                    <input type="text" name="listVcrSummary" value="<?= h($this->dto->Summary ?? '') ?>">
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
            <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
            <input type="hidden" name="JdId" value="<?= h($tokenKey) ?>">
            <table class="UpdTbl">
                <?php $vcrIdSW = 0; $VcrSearchedData = $this->dto->VcrSearchedData;
                        $creditAmount = 0; $debitAmount = 0; $creditName = ''; $debitName = '';
                ?>
                <?php foreach ($VcrSearchedData as $vcrRowNo => $row):  ?>
                    <?php if($row['side'] === 'credit') {
                        $creditAmount = (int)$row['amount']??'0';
                        $creditName = $row['name']??'';
                    } else {
                        $debitAmount = (int)$row['amount']??'0';
                        $debitName = $row['name']??'';
                    }
                    ?>
                    <?php if ($vcrIdSW != $row['voucher_id']): ?>
                            <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                <th style=" width: 5%;" >伝票No</th>
                                <th style=" width: 11%;" >日付</th>
                                <th style=" width: 13%;" >借方科目</th>
                                <th style=" width: 10%;">借方金額</th>
                                <th style=" width: 10%;">貸方金額</th>
                                <th style=" width: 13%;">貸方科目</th>
                                <th style=" width: 15%;">摘要<br>
                                    <input style="width : 95%;" type="text" name="vcrUpdDt[<?= $vcrRowNo ?>][summary]" 
                                        value="<?= h($row['summary']) ?? '' ?>"
                                    >                                    
                                </th>
                                <th style=" width: 22%;">
                                    <button name="vcrUpdate" type="submit"
                                        onclick="return confirm('伝票修正欄の内容をデータベースに登録します。元に戻せません。\n本当に変更してもよろしいですか？');" 
                                        class="btn btn-danger"
                                        value="<?= h('vcrUpdate') ?>">修正実行
                                    <button name="vcrDelete" type="submit" 
                                        onclick="return confirm('この伝票を削除すると、紐づく明細データもすべて削除されます。\n本当に削除してもよろしいですか？');" 
                                        class="btn btn-danger"
                                        value="<?= h('vcrDelete') ?>">伝票削除
                                    </button>
                                </th>
                            </tr>
                    <?php endif; ?>
                    <tr>
                    <?php if ($vcrIdSW  != $row['voucher_id']): ?>
                        <?php $vcrIdSW   = (int)$row['voucher_id']; ?>
                            <td  style="font-weight: bold; text-align: center;">
                                <?= h($row['voucher_id']) ?>
                            </td>
                            <td style="font-weight: bold; text-align: center;">
                                <?= h($row['voucher_date']??'') ?>
                            </td>
                    <?php else: ?>
                            <td></td>
                            <td></td>
                    <?php endif; ?>
                            <td>
                                <?php if($row['side'] === 'debit'): ?>
                                    <select  style=" width: 95%;"  name="vcrUpdDt[<?= $vcrRowNo ?>][account_id]" required >
                                        <option value="">選択してください</option>
                                            <?php foreach($this->dto->accounts as $a): ?>
                                                <option value="<?= h($a['id']) ?>" 
                                                    <?= (isset($row['account_id']) && $row['account_id'] == $a['id']) ? 'selected' : '' ?>>
                                                    <?= h($a['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="vcrUpdDt[<?= $vcrRowNo ?>][debitName]" value="<?= h($debitName ?? '') ?>">
                                <?php endif; ?>
                            </td>
                            <td  style="font-weight: bold; text-align: right;">
                                <?php if($row['side'] === 'debit'): ?>
                                    <input style="width : 95%;" type="text" name="vcrUpdDt[<?= $vcrRowNo ?>][amount]" value="<?= h($row['amount']) ?? '' ?>">
                                <?php endif; ?>
                            </td>
                            <td  style="font-weight: bold; text-align: right;">
                                <?php if($row['side'] === 'credit'): ?>
                                    <input style="width : 95%;" type="text" name="vcrUpdDt[<?= $vcrRowNo ?>][amount]" value="<?= h($row['amount']) ?? '' ?>">
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['side'] === 'credit'): ?>
                                    <select style=" width: 95%;" name="vcrUpdDt[<?= $vcrRowNo ?>][account_id]" required >
                                        <option value="">選択してください</option>
                                            <?php foreach($this->dto->accounts as $a): ?>
                                                <option value="<?= h($a['id']) ?>" 
                                                    <?= (isset($row['account_id']) && $row['account_id'] == $a['id']) ? 'selected' : '' ?>>
                                                    <?= h($a['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="vcrUpdDt[<?= $vcrRowNo ?>][CreditName]" value="<?= h($creditName ?? '') ?>">
                                <?php endif; ?>                           
                            </td>
                            <td  style="font-weight: bold; text-align: center;">



                                    <input style="width : 95%;" type="text" name="vcrUpdDt[<?= $vcrRowNo ?>][jd_summary]" 
                                        value="<?= h($row['jd_summary'] ?? '') ?>"
                                    >                                    


                                <!-- <?php //echo  h($row['summary']??'') ?> -->
                            </td>
                            <td>
                              <div class="button-container">
                                <input type="hidden" name="vcrUpdDt[<?= $vcrRowNo ?>][JdId]" value="<?= h($row['JdId'] ?? 0) ?>">
                                <input type="hidden" name="vcrUpdDt[<?= $vcrRowNo ?>][id]" value="<?= h($row['id'] ?? '') ?>">
                                <input type="hidden" name="vcrUpdDt[<?= $vcrRowNo ?>][voucher_id]" value="<?= h($row['voucher_id'] ?? '') ?>">
                                <input type="hidden" name="vcrUpdDt[<?= $vcrRowNo ?>][side]" value="<?= h($row['side'] ?? '') ?>">
                                <button name="VcrAddDebit" type="submit" value="<?= h($vcrRowNo ?? '') ?>">借方行追加</button>
                                <button name="VcrAddCredit" type="submit" value="<?= h($vcrRowNo ?? '') ?>">貸方行追加</button>
                                <button name="VcrDetailLineDel" type="submit" value="<?= h($vcrRowNo ?? '') ?>">行削除</button>
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
            <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
            <table class="UpdTbl">
                <?php if (empty($this->dto->vcrListResult)): ?>
                    <tr>
                        <td colspan="9">検索条件に一致する伝票が見つかりませんでした。</td>
                    </tr>
                <?php endif; ?>
                <?php (int)$vcrIdSW = 0; $vcrListResult = $this->dto->vcrListResult; ?>
                <?php foreach ($vcrListResult as $vcrId => $row): $creditAmount = 0; $debitAmount = 0; $creditName = ''; $debitName = ''; ?>
                    <?php if($row['side'] === 'credit') {
                        $creditAmount = (int)$row['amount']??'0';
                        $creditName = $row['name']??'';
                    } else {
                        $debitAmount = (int)$row['amount']??'0';
                        $debitName = $row['name']??'';
                    }
                    ?>
                    <?php if ($vcrIdSW !== (int)$row['voucher_id']): ?>
                            <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                <th width 5%>伝票No</th>
                                <th width 8%>日付</th>
                                <th>借方科目</th>
                                <th>借方金額</th>
                                <th>貸方金額</th>
                                <th>貸方科目</th>
                                <th>摘要<br>
                                    <?= h($row['summary'] )?>                                    
                                </th>
                                <th>
                                    <?php if($this->dto->vcrListResult[$vcrId]['voucher_id'] !== '999999999999'): ?>
                                         <button name="vcrUpdateNo" type="submit" value="<?= h($row['voucher_id']) ?>">修正</button>
                                    <?php endif; ?>
                                </th>
                            </tr>
                    <?php endif; ?>
                        <tr>
                    <?php if (!empty($row['JdId'])): ?>
                        <?php if ((int)$vcrIdSW !== (int)$row['voucher_id']): ?>
                            <?php (int)$vcrIdSW = (int)$row['voucher_id']; ?>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($row['voucher_id']) ?>
                                </td>
                                <td style="font-weight: bold; text-align: center;">
                                    <?= h($row['voucher_date']??'') ?>
                                </td>
                        <?php else: ?>
                                <td></td>
                                <td></td>
                        <?php endif; ?>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($debitName) ?>
                                </td>
                                <td  style="font-weight: bold; text-align: right;">
                                    <?= h($debitAmount) ?>
                                </td>
                                <td  style="font-weight: bold; text-align: right;">
                                    <?= h($creditAmount) ?>
                                </td>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($creditName) ?>
                                </td>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($row['jd_summary']??'') ?>
                                </td>
                                <td  style="font-weight: bold; text-align: center;">
                                    <?= h($row['total_debit']??'') ?>
                                </td>
                    <?php else: ?>
<!--                                <td></td>
                                <td></td>
                                <td style="font-weight: bold; text-align: center;">
                                    合計</td>
                                <td style="font-weight: bold; text-align: right;">
                                    <?= h($row['debit_total']??'') ?>
                                </td>
                                <td style="font-weight: bold; text-align: right;">
                                    <?= h($row['credit_total']??'') ?>
                                </td>
                                <td></td>
                                <td style="font-weight: bold; text-align: right;">
                                    ステータス
                                </td>
                                <td style="color: #ff0073; font-weight: bold; text-align: center;">
                                    <?= h($row['credit_total']??'') === h($row['debit_total']??'') ? ' ': '貸借不一致' ?>
-->                                </td>
                    <?php endif; ?>
                            </tr>
                <?php endforeach; ?>
            </table>
        </form>
</td>
</tr>
</table>