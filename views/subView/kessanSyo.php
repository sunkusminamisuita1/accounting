<h3>試算表表示：<?= $this->dto->reportType ?></h3>
            

                <div class="expense-panel">
                    <div class="expense-panel-inner">
                        
                        <!-- テーブルにCSSクラス「tb-style」を適用 -->
                         <?php $zenkiTotalAmount = 0; $zTotalAmount = 0; ?>
                        <table class="tb-style">
                            <?php if ($this->dto->reportSubType !== '前期比較') : ?>
                            <?php   echo h($this->dto->reportSubType); ?>期間　<?php echo h($this->dto->from) . " : " . h($this->dto->to); ?>
                            <tbody>
                                <tr>

                                    <th>経費科目</th>
                                    <th>金額</th>

                                </tr>
                                    <?php 
                                          $totalAmount = 0;
                                          foreach ($this->dto->keihiItiran as $key => $row) : ?>
                                <tr>
                                    <!-- クラス「col-name」でセンター寄せ -->
                                    

                                    <td class="col-name">
                                        <?php echo h($row['keihiAccountName']); ?>
                                    </td>

                                    <!-- クラス「col-amount」で右詰め -->
                                    <td class="col-amount">
                                        <?php 
                                        // 3桁カンマ区切りにし、前後に ¥ と .- を付与
                                        $formatted_debit = number_format((int)$row['keihiAmountTotal']);
                                        echo h("¥{$formatted_debit}.-");
                                        $totalAmount += (int)$row['keihiAmountTotal'];
                                        ?>
                                    </td>



                                </tr>
                                    <?php endforeach; ?>

                                <tr class="total-row" style="background-color: #e8efea; font-weight: bold;">
                                    <!-- クラス「col-name」でセンター寄せ -->
                                    
 

                                    <td class="col-name">
                                        <?php echo h('金 額  合 計'); ?>
                                    </td>

                                    <td class="col-amount">
                                        <?php 
                                        // 3桁カンマ区切りにし、前後に ¥ と .- を付与
                                        $formatted_debit = number_format((int)$totalAmount);
                                        echo h("¥{$formatted_debit}.-");
                                        ?>
                                    </td>

                                </tr>
                            </tbody>
                            <?php endif; ?>
                            <?php if ($this->dto->reportSubType === '前期比較') : ?>
                            <tbody>



                                <tr>
                                    <th colspan="2">
                                        前期　<?= h($this->dto->zenki_from) . " : " . h($this->dto->zenki_to); ?>
                                    </th>
                                    <th colspan="2">
                                        基準年　<?= h($this->dto->from) . " : " . h($this->dto->to); ?>
                                    </th>

                                </tr>
                                <tr>

                                    <th>経費科目</th>
                                    <th>金額</th>
                                    <th>経費科目</th>
                                    <th>金額</th>

                                </tr>
                                    <?php 
                                          $totalAmount = 0;
                                          $totalZenkiAmount = 0;
                                          foreach ($this->dto->keihiItiran as $key => $row) : ?>
                                <tr>
                                    <!-- クラス「col-name」でセンター寄せ $this->dto->keihiZenkiItiran-->                                    

                                    <td class="col-name">
                                        <?= h($row['keihiAccountName'])??''; ?>
                                    </td>

                                    <!-- クラス「col-amount」で右詰め -->
                                    <td class="col-amount">
                                        <?php 
                                        // 3桁カンマ区切りにし、前後に ¥ と .- を付与
                                        $zformatted_debit = number_format((int)$row['zKeihiAmountTotal']);
                                        echo h("¥{$zformatted_debit}.-");
                                        $zTotalAmount += (int)$row['zKeihiAmountTotal'];
                                        ?>
                                    </td>








                                    <td class="col-name">
                                        <?= h($row['keihiAccountName'])??''; ?>
                                    </td>

                                    <!-- クラス「col-amount」で右詰め -->
                                    <td class="col-amount">
                                        <?php 
                                        // 3桁カンマ区切りにし、前後に ¥ と .- を付与
                                        $zenkiFormatted_debit = number_format((int)$row['keihiAmountTotal']);
                                        echo h("¥{$zenkiFormatted_debit}.-");
                                        $totalAmount += (int)$row['keihiAmountTotal'];
                                        ?>
                                    </td>


                                </tr>
                                
                                    <?php endforeach; ?>





                                <tr class="total-row" style="background-color: #e8efea; font-weight: bold;">
                                    <!-- クラス「col-name」でセンター寄せ -->
                                    
 

                                    <td class="col-name">
                                        <?php echo h('前 期  金 額  合 計'); ?>
                                    </td>

                                    <td class="col-amount">
                                        <?php 
                                        // 3桁カンマ区切りにし、前後に ¥ と .- を付与
                                        $zformatted_debit = number_format((int)$zTotalAmount);
                                        echo h("¥{$zformatted_debit}.-");
                                        ?>
                                    </td>



                                    <td class="col-name">
                                        <?php echo h('基 準  金 額  合 計'); ?>
                                    </td>

                                    <td class="col-amount">
                                        <?php 
                                        // 3桁カンマ区切りにし、前後に ¥ と .- を付与
                                        $formatted_debit = number_format((int)$totalAmount);
                                        echo h("¥{$formatted_debit}.-");
                                        ?>
                                    </td>







                                </tr>                             



                            </tbody>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
</div>