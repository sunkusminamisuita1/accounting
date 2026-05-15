<style>
    .ProcSlct, .ProcSlct td { border: none !important; }
    .UpdTbl { border-collapse: collapse; width: 100%; border: 1px solid #000000; } /* 幅は中身に合わせるのが一般的 */
    .ProcSlct button { cursor: pointer; padding: 5px 15px; }
    th,td {  padding: 0.6em; border: 1px solid #000000; }
</style>
<table class="UpdTbl">
<tr>
<td style="width: 50%; vertical-align: top;">
    <h1>仕分伝票修正-対象伝票検索</h1>
    <?php
        require_once ROOT_PATH . '/views/lib/ProcSlct.php';
        $details = $this->VoucherDto->DtoDetails;
    ?>
    <?php if (!empty($this->VoucherDto->ErrData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->VoucherDto->ErrData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <br><hr>
    <h3>単独検索(伝票No，取引日付，取引金額，摘要欄あいまい検索)は、<br>
    １つ以上の検索条件を入力して検索ボタンを押してください。
    </h3>
    <form method="POST" action="index.php?route=voucher.list">
        <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
        <table class="UpdTbl">
            <tr>
                <td>
                    伝票No
                </td>

                <td>
                    <input type="text" name="ListVcrNum" value="<?= h($_POST['ListVcrNum'] ?? '') ?>">
                </td>

            <tr>　
            </tr>

            <tr>
                <td>
                    日付        
                </td>

                <td>
                    <input type="date" name="ListVcrDate" value="<?= h($_POST['ListVcrDate'] ?? '') ?>">
                </td>
            </tr>

            <tr>
                <td>
                    期間        
                </td>
                <td>
                    開始<input type="date" name="LstVcrSerchStartDate" value="<?= h($_POST['ListVcrStartDate'] ?? '') ?>">
                
                
                    　　　終了<input type="date" name="ListVcrSerchEndDate" value="<?= h($_POST['ListVcrSerchEndDate'] ?? '') ?>">
                </td>
            </tr>

            <tr>　
            </tr>

            <tr>
                <td>
                    摘要（曖昧検索）
                </td>

                <td>
                    <input type="text" name="ListVcrSummary" value="<?= h($_POST['ListVcrSummary'] ?? '') ?>">
                </td>
            </tr>
        </table>
        <br>
        <button name="SimpleSearch" type="submit" value="<?= h('SimpleSearch') ?>">検索</button>
    </form>
    <hr>
</td>


<td>
    <h3>検索結果</h3>
        <form method="POST" action="index.php?route=voucher.update">
            <input type="hidden" name="csrfTokenKey" value="<?= h($TokenKey) ?>">
<!--            <button name="CompoundSearch" type="submit" value="<?= h('CompoundSearch') ?>">複合検索</button>　-->
            <table class="UpdTbl">
                <?php if (empty($this->VoucherDto->VcrListResult)): ?>
                    <tr>
                        <td colspan="9">検索条件に一致する伝票が見つかりませんでした。</td>
                    </tr>
                <?php endif; ?>
                <?php $VcrIdSW = 0; $VcrListResult = $this->VoucherDto->VcrListResult; ?>
                <?php foreach ($VcrListResult as $VcrId => $Row): $CreditAmount = 0; $DebitAmount = 0; $CreditName = ''; $DebitName = ''; ?>
                    <?php if($Row['side'] === 'credit') {
                        $CreditAmount = (int)$Row['amount']??'0';
                        $CreditName = $Row['name']??'';
                    } else {
                        $DebitAmount = (int)$Row['amount']??'0';
                        $DebitName = $Row['name']??'';
                    }
                    ?>
                    <?php if ($VcrIdSW !== $Row['voucher_id']): ?>
                            <tr style="background-color: #e0e0e1; font-weight: bold; text-align: center;">
                                <th>伝票No</th>
                                <th>日付</th>
                                <th>貸方科目</th>
                                <th>貸方金額</th>
                                <th>借方金額</th>
                                <th>借方科目</th>
                                <th>摘要</th>
                                <th>
                                    <?php if($this->VoucherDto->VcrListResult[$VcrId]['voucher_id'] !== '999999999999'): ?>
                                         <button name="VcrUpdate" type="submit" value="<?= h('VcrUpdate') ?>">修正</button>
                                    <?php endif; ?>
                                </th>
                            <!--    <th>
                                    <button name="VcrUpdate" type="submit" value="<?= h('VcrUpdate') ?>">修正</button>
                                </th> -->
                            </tr>
                    <?php endif; ?>
                        <tr>
                <?php if (!empty($Row['JdId'])): ?>
                    <?php if ($VcrIdSW !== $Row['voucher_id']): ?>
                        <?php $VcrIdSW = $Row['voucher_id']; ?>
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
                                <?= h($CreditName) ?>
                            </td>
                            <td  style="font-weight: bold; text-align: right;">
                                <?= h($CreditAmount) ?>
                            </td>
                            <td  style="font-weight: bold; text-align: right;">
                                <?= h($DebitAmount) ?>
                            </td>
                            <td  style="font-weight: bold; text-align: center;">
                                <?= h($DebitName) ?>
                            </td>
                            <td  style="font-weight: bold; text-align: center;">
                                <?= h($Row['summary']??'') ?>
                            </td>
                            <td  style="font-weight: bold; text-align: center;">
                                <?= h($Row['total_debit']??'') ?>
                            </td>
                        <!--    <td  style="font-weight: bold; text-align: center;">
                                <?= h($Row['total_credit']??'') ?>
                            </td> -->
                <?php else: ?>
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold; text-align: center;">
                                合計</td>
                            <td style="font-weight: bold; text-align: right;">
                                <?= h($Row['credit_total']??'') ?>
                            </td>
                            <td style="font-weight: bold; text-align: right;">
                                <?= h($Row['debit_total']??'') ?>
                            </td>
                            <td></td>
                            <td style="font-weight: bold; text-align: right;">
                                ステータス
                            </td>
                            <td style="color: #ff0073; font-weight: bold; text-align: center;">
                                <?= h($Row['credit_total']??'') === h($Row['debit_total']??'') ? ' ': '貸借不一致' ?>
                            </td>
                        <!--    <td></td> -->
                <?php endif; ?>
                        </tr>
                <?php endforeach; ?>
            </table>
        </form>
</td>
</tr>
</table>