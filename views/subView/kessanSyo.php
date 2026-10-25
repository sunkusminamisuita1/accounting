<h3>試算表表示：<?= $this->dto->reportType ?></h3>
            

                <div class="expense-panel">
                    <div class="expense-panel-inner">
                        
                        <!-- テーブルにCSSクラス「tb-style」を適用 -->
                        <table class="tb-style">
                            <tbody>
                                <tr>
                                    <th>日付</th>
                                    <th>経費科目</th>
                                    <th>金額</th>
                                    <th>相手勘定科目</th>
                                </tr>
                                    <?php foreach ($this->dto->keihiItiran as $key => $row) : ?>
                                <tr>
                                    <!-- クラス「col-name」でセンター寄せ -->
                                    <td class="col-name">
                                        <?= h($row['date']) ?>
                                    </td>
                                    <td class="col-name">
                                        <?= h($row['name']) ?>
                                    </td>
                                    <!-- クラス「col-amount」で右詰め -->
                                    <td class="col-amount">
                                        <?php 
                                        // 3桁カンマ区切りにし、前後に ¥ と .- を付与
                                        $formatted_debit = number_format((int)$row['debit']);
                                        echo h("¥{$formatted_debit}.-");
                                        ?>
                                    </td>
                                    <td class="col-name">
                                        <?= h($row['aiteKamoku']) ?>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
</div>