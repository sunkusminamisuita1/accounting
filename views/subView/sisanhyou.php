        <h3>試算表表示：<?= $this->dto->reportType ?></h3>
            <form action="index.php?route=home" method="post">

                <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
<?php
//echo "現在のルート2: " . h($requestRoute) . "<br>";

                if ($this->dto->reportType) {

                    if($this->dto->reportType === getujiSisanhyou){
                        $this->dto->from = date('Y-m', strtotime($this->dto->from));
?>
                        年月：<input type="month" name="from"
                        value="<?= h($this->dto->from ) ?>" placeholder="例: 2025-01">
                        
<?php               } 
                    if($this->dto->reportType  === nenjiSisanhyou){
?>
                        年：<input type="number" name="nenji_nen" min='1900' max='2100'
                        value="<?= h($this->dto->post['nenji_nen'] ?? "") ?>" placeholder="例: 2025">
                        
<?php
                        $this->dto->from = isset($_POST['nenji_nen']) ? $_POST['nenji_nen'] . '0101' : "";
                    };
                    if($this->dto->reportType  === ruisekiSisanhyou){
?>
                        試算表期日：<input type="date" name="to" value="<?= h($this->dto->to ) ?>" placeholder="例: 2025-01-01">
                        
<?php               }
                    if($this->dto->reportType  === zenkiHikaku){
?>
                        基準年：<input type="number" name="kijyun_nen" min='1900' max='2100'
                        value="<?= h($this->dto->post['kijyun_nen'] ?? "") ?>"  placeholder="例: 2025">
                        
<?php                   $this->dto->from = isset($_POST['kijyun_nen'])?$_POST['kijyun_nen'] . '0101':"";
                    };
                    if($this->dto->reportType  === kikanSisanhyou){
?>
                        開始日：<input type="date" name="from" value="<?= h($this->dto->from) ?>" placeholder="例: 2025-01-01">
                        終了日：<input type="date" name="to" value="<?= h($this->dto->to ) ?>" placeholder="例: 2025-01-01">
<?php
                    };
                }
?>
			            
			        　<button name="KeisanJikkou" type="submit" value="Exec">試算表表示</button>
		    </form>

        <div class="report-result-layout">
            <div class="report-main">


























<?php
    if (in_array($this->dto->reportType, 
            [getujiSisanhyou, nenjiSisanhyou, kikanSisanhyou, ruisekiSisanhyou, zenkiHikaku])) {
?>
        <p>抽出期間： <?= h($this->dto->from) ?> 〜 <?= h($this->dto->to ) ?></p>
        <table>
	        <thead>
		        <tr>
			        <th>科目</th>
			        <th>借方</th>
			        <th>貸方</th>
			        <th>残高</th>
		        </tr>
	        </thead>
	        <tbody>
<?php
    foreach ($this->dto->viewResult as $row){
        if ($row['row_type'] === 'account'){
?>
		        <tr>
			        <td class="text-left"><?= h($row['name']) ?></td>
			        <td><?= number_format($row['debit']) ?></td>
			        <td><?= number_format($row['credit']) ?></td>
			        <td><?= number_format($row['balance']) ?></td>
		        </tr>
<?php
        }elseif ($row['row_type'] === 'total'){ ?>
		        <tr>
			        <th><?= h($row['label']) ?></th>
			        <th><?= number_format($row['debit']) ?></th>
			        <th><?= number_format($row['credit']) ?></th>
			        <th></th>
		        </tr>
<?php
        }
    };
?>
	        </tbody>
        </table>
<?php
    }
    if (in_array($this->dto->reportType,[ruisekiSisanhyou])):
?>
        <p>期間： <?= h($this->dto->from) ?> 〜 <?= h($this->dto->to ) ?></p>
        <table>
	        <thead>
	            <tr>
		            <th>科目</th>
		            <th>残高</th>
	            </tr>
	        </thead>
	    <tbody>
<?php
        foreach ($this->dto->viewResult as $row):
            if ($row['row_type'] === 'account'):
?>
	            <tr>
		            <td class="text-left"><?= h($row['name']) ?></td>
		            <td><?= number_format($row['balance']) ?></td>
	            </tr>
<?php
            elseif ($row['row_type'] === 'subtotal'): 
?>
                <tr>
		            <th class="text-left"><?= h($row['label']) ?></th>
		            <th><?= number_format($row['balance']) ?></th>
	            </tr>
<?php
                elseif ($row['row_type'] === 'total'):
?>
                    <tr style="background:#eee;">
		                <th class="text-left"><?= h($row['label']) ?></th>
		                <th><?= number_format($row['balance']) ?></th>
	                </tr>
<?php
            endif;
        endforeach;
?>
	    </tbody>
        </table>
<?php
    endif;
?>
<script>
// クリックで該当フィールドにフォーカス
document.addEventListener('DOMContentLoaded', function(){
    var list = document.getElementById('validation-list');
    if(!list) return;
    list.addEventListener('click', function(e){
        var li = e.target.closest('li[data-field]');
        if(!li) return;
        var field = li.getAttribute('data-field');
        if(!field) return;
        // try to find element(s) by name
        var el = document.querySelector('[name="'+field+'"]');
        if(el){
            el.focus();
            if(el.select) try{ el.select(); }catch(err){}
            return;
        }
        // special handling for names that might be used multiple times (e.g., radio groups 'reportType')
        var els = document.getElementsByName(field);
        if(els && els.length){
            els[0].focus();
        }
    });
});
</script>
<?php
    if (in_array($this->dto->reportType,[zenkiHikaku])){
?>
        <p>当期期間： <?= h($this->dto->from) ?> 〜 <?= h($this->dto->to) ?></p>
        <p>前期期間： <?= h($this->dto->zenki_from) ?? '' ?> 〜 <?= h($this->dto->zenki_to) ?? '' ?></p>
        <table>
	        <thead>
		        <tr>
			        <th>科目</th>
			        <th>当期残高</th>
			        <th>前期残高</th>
			        <th>増減</th>
		        </tr>
	        </thead>
	        <tbody>
<?php
        foreach ($this->dto->viewResult as $row){
 ?>
		        <tr>
			        <td class="text-left"><?= h($row['name']) ?></td>
			        <td><?= number_format($row['cur_balance']) ?></td>
			        <td><?= number_format($row['prev_balance']) ?></td>
			        <td><?= number_format($row['diff']) ?></td>
		        </tr>
<?php
        }
    }
?>

<?php
    for($idx=0; $idx < 10; $idx++){
        $keihiItiran[$idx] = ['name'=> 'name'. $idx,  'amount'=> 'amount' . $idx];
    }
?>

	        </tbody>
        </table>

            </div>
        <div class="expense-panel">
            <div class="expense-panel-inner">
                   
                <!-- テーブルにCSSクラス「tb-style」を適用 -->
                <table class="tb-style">
                    <tbody>
                        <tr>
                            <th>経費科目</th>
                            <th>金額</th>
                        </tr>
                            <?php foreach ($this->dto->keihiItiran as $key => $row) : ?>
                        <tr>
                            <!-- クラス「col-name」でセンター寄せ -->
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
                        </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>